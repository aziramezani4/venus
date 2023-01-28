<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CheckotpRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Customer\CustomerResource;
use App\Http\Resources\Verify\VerifyResource;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Verify;
use DateTime;
use http\Env\Response;
use Illuminate\Support\Facades\DB;
use Notification;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;
use Pamenary\LaravelSms\Laravel\Facade\Sms;
use Validator;
use Exception;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class AuthController extends Controller
{
    /**
     * @OA\Info(title="My First API", version="0.1")
     */
    /**
     * @OA\Post(
     * path="/api/customer/signup",
     * summary="Sign in",
     * description="Login by phone",
     * operationId="authLogin",
     * tags={"auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="phone user credentials",
     *    @OA\JsonContent(
     *       required={"phone"},
     *       @OA\Property(property="phone", type="string", format="number", example="09172549365"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
     *        )
     *     )
     * )
     */
    public function signup(LoginRequest $request)
    {

        try {
            $verify = Verify::where('phone', $request->phone)->first();
            if (!$verify) {
                $new_verify = Verify::create([
                    'phone' => $request->phone
                ]);


                $otp_code = rand(100000, 999999);
                $to = $request->phone;

                $url = 'https://console.melipayamak.com/api/send/shared/35ea3684ce1b446ebc29cf956a332197';
                $data = array('bodyId' => 120805, 'to' => $to, 'args' => ["$otp_code"]);
                $data_string = json_encode($data);
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

                // Next line makes the request absolute insecure
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// Use it when you have trouble installing local issuer certificate
// See https://stackoverflow.com/a/31830614/1743997

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array('Content-Type: application/json',
                        'Content-Length: ' . strlen($data_string))
                );
                $result = curl_exec($ch);
                curl_close($ch);
// to debug
// if(curl_errno($ch)){
//     echo 'Curl error: ' . curl_error($ch);
// }




                $new_verify->update([
                    'otp_code' => Hash::make($otp_code),
                ]);

                $data = $new_verify->phone;
                $status = 200;
                $message = 'otp_code send successfully';
                $isSuccess =true;
                $errors = [];

                return response_json($data,$status,$message,$isSuccess,$errors);

            } else {
                $user = Customer::where('phone_number', $request->phone)->first();
                $verify->update([
                    'account_id' => $user->id,
                ]);
                $date = new DateTime();
                $user->update([
                    'last_login' => $date,
                ]);
                $data = $verify;
                $status = 200;
                $message = 'login successfully';
                $isSuccess =true;
                $errors = [];

                return response_json($data,$status,$message,$isSuccess,$errors);

            }
        } catch (Exception $e) {

            $data = [];
            $status = 200;
            $message = 'Internal Service Error';
            $isSuccess =false;
            $errors = [
                'message' => $e->getMessage(),
            ];

            return response_json($data,$status,$message,$isSuccess,$errors);

        }
    }

    /**
     * @OA\Post(
     * path="/api/customer/signup/check/otp",
     * summary="Check OTP",
     * description="Check by phone and otp_code",
     * tags={"auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="phone user credentials",
     *    @OA\JsonContent(
     *       required={"phone_number","otp_code"},
     *       @OA\Property(property="phone_number", type="string", format="number", example="09172549365"),
     *       @OA\Property(property="otp_code", type="string", format="number", example="1254365"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
     *        )
     *     )
     * )
     */
    public function check_otp(CheckotpRequest $request): JsonResponse
    {
        $verify = Verify::where('phone',$request->phone_number)->first();

        $exist_customer=DB::table('customers')->where('phone_number',$request->phone_number)->first();
            if (Hash::check($request->otp_code,$verify->otp_code )){

          if(!$exist_customer) {
              $customer = Customer::create([
                  'phone_number' => $request->phone_number,
              ]);


              DB::table('verifies')->where('id', $verify->id)->update(['customer_id' => $customer->id]);
              $profile_status = $customer->profile_status;

              $data = [
                  'customer_id' => $customer->id,
                  'phone' => $customer->phone_number,
                  'profile_status' => $profile_status ? true : false,
                  'token' => $customer->createToken('auth-token', ['*'], now()->addDay())->plainTextToken,
              ];
              $status = 200;
              $message = 'check otp Verified successfully';
              $isSuccess = true;
              $errors = null;

              return response_json($data, $status, $message, $isSuccess, $errors);
          }else{

              DB::table('verifies')->where('id', $verify->id)->update(['customer_id' => $exist_customer->id]);
              $data = [
                  'customer_id' => $exist_customer->id,
                  'phone' => $exist_customer->phone_number,
                  'profile_status' => $exist_customer->profile_status ? true : false,
                  'token' => $verify->createToken('auth-token', ['*'], now()->addDay())->plainTextToken,
              ];
              $status = 200;
              $message = 'check otp Verified successfully';
              $isSuccess = true;
              $errors = null;

              return response_json($data, $status, $message, $isSuccess, $errors);
          }
        } else {
            $data = [];
            $status = 200;
            $message = 'OTP is not valid';
            $isSuccess =false;
            $errors = ['message' => 'OTP is not valid'];

            return response_json($data,$status,$message,$isSuccess,$errors);

        }
    }
    /**
     * @OA\Post(
     * path="/api/customer/check/password",
     * summary="Check password",
     * description="Check by phone and password",
     * tags={"auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="phone user credentials",
     *    @OA\JsonContent(
     *       required={"phone_number","otp_code"},
     *       @OA\Property(property="phone_number", type="string", format="number", example="09172549365"),
     *       @OA\Property(property="password", type="string", format="number", example="1254365"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
     *        )
     *     )
     * )
     */
    public function check_password(Request $request): JsonResponse
    {
        $customer = Customer::where('phone_number',$request->phone_number)->first();

        if (Hash::check($request->password,$customer->password)) {

            $data = [
                'customer'=>$customer,
                'token' => $customer->createToken('auth-token', ['*'], now()->addDay())->plainTextToken,
            ];
            $status = 200;
            $message = 'Account Verified successfully';
            $isSuccess =true;
            $errors = null;

            return response_json($data,$status,$message,$isSuccess,$errors);

        } else {
            $data = [];
            $status = 200;
            $message = 'Password is not valid';
            $isSuccess =false;
            $errors = ['message' => 'Password is not valid'];

            return response_json($data,$status,$message,$isSuccess,$errors);

        }
    }

    /**
     * @OA\Post(
     * path="/api/customer/register/{verify}",
     * summary="Sign up",
     * description="Register by phone",
     * operationId="authRegister",
     * tags={"auth"},
     * security={ {"bearer": {} }},
     * @OA\Parameter(
     *    description="ID of verify",
     *    in="path",
     *    name="verify",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="information user credentials",
     *    @OA\JsonContent(
     *       required={"firstname","user_name","address","phone","national_code","gender","birthday"},
     *       @OA\Property(property="first_name", type="string", format="string", example="john"),
     *       @OA\Property(property="last_name", type="string", format="string", example="doe"),
     *       @OA\Property(property="phone_number", type="number", format="number", example="09362561425"),
     *       @OA\Property(property="password", type="string", format="number", example="test"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong information. Please try again")
     *        )
     *     )
     * )
     */

    public function register(Verify $verify,RegisterRequest $request): JsonResponse|CustomerResource
    {
        try {
            $exist_customer = DB::table('customers')->where('phone_number', $verify->phone)->first();
            if(!$exist_customer) {
                $customer = Customer::create([
                    'phone_number' => $verify->phone,
                    'user_name' => $request->user_name,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'password' => Hash::make($request->password),
                ]);

                DB::table('verifies')->where('id', $verify->id)->update(['customer_id' => $customer->id]);

                $data = new CustomerResource($customer);
                $status = 200;
                $message = 'Customer create successfully';
                $isSuccess = true;
                $errors = null;

                return response_json($data, $status, $message, $isSuccess, $errors);
            }else{
                $data = [];
                $status = 200;
                $message = 'Customer exists';
                $isSuccess = true;
                $errors = null;

                return response_json($data, $status, $message, $isSuccess, $errors);
            }
        } catch (Exception $e) {
            $data = [];
            $status = 200;
            $message = 'Internal server error';
            $isSuccess =false;
            $errors = ['message' => $e->getMessage()];

            return response_json($data,$status,$message,$isSuccess,$errors);
        }
    }
    /**
     * @OA\Post(
     * path="/api/customer/new/password/{verify}",
     * summary="Reset password",
     * description="Resset by password",
     * operationId="authRegister",
     * tags={"auth"},
     * security={ {"bearer": {} }},
     * @OA\Parameter(
     *    description="ID of verify",
     *    in="path",
     *    name="verify",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="information user credentials",
     *    @OA\JsonContent(
     *       required={"password","re_password"},
     *       @OA\Property(property="password", type="string", format="string", example="john"),
     *       @OA\Property(property="re_password", type="string", format="string", example="john"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong information. Please try again")
     *        )
     *     )
     * )
     */

    public function new_password(Verify $verify,Request $request): JsonResponse|CustomerResource
    {
        $customer = Customer::where('phone_number',$verify->phone)->first();
        try {
            if($customer) {
                if ($request->password == $request->re_password) {
                    $customer->update([
                        'password' => Hash::make($request->password),
                    ]);

                    $data = new CustomerResource($customer);
                    $status = 200;
                    $message = 'Password change successfully';
                    $isSuccess = true;
                    $errors = null;

                    return response_json($data, $status, $message, $isSuccess, $errors);
                } else {
                    $data = [];
                    $status = 200;
                    $message = 'Password Incorrect';
                    $isSuccess = false;
                    $errors = null;

                    return response_json($data, $status, $message, $isSuccess, $errors);
                }
            }else{
                $data = [];
                $status = 200;
                $message = 'Customer does not exist';
                $isSuccess = false;
                $errors = null;

                return response_json($data, $status, $message, $isSuccess, $errors);

            }
        } catch (Exception $e) {
            $data = [];
            $status = 200;
            $message = 'Internal server error';
            $isSuccess = false;
            $errors = $e;

            return response_json($data, $status, $message, $isSuccess, $errors);
        }
    }
}
