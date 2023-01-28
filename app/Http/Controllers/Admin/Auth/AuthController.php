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
use Pamenary\LaravelSms\Laravel\Facade\Sms;
use Validator;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class AuthController extends Controller
{
    public function signup(LoginRequest $request)
    {

        try {
            $verify = Verify::where('phone', $request->phone)->first();
            if (!$verify) {
                $new_verify = Verify::create([
                    'phone' => $request->phone
                ]);
                

//                $otp_code = rand(100000, 999999);
//                $to = $request->phone;

//                $code = rand(100000, 999999);
//                $to = $request->phone;
//                $new_verify->send_sms($to,$code);
//                $code = rand(100000, 999999);
//                $url = 'https://console.melipayamak.com/api/send/simple/35ea3684ce1b446ebc29cf956a332197';
//                $data = array('from' => '50004001445999', 'to' => '09171022166', 'text' =>'test');
//                $data_string = json_encode($data);
//                $ch = curl_init($url);
//                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
//                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
//
//// Next line makes the request absolute insecure
//                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//// Use it when you have trouble installing local issuer certificate
//// See https://stackoverflow.com/a/31830614/1743997
//
//                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//                curl_setopt($ch, CURLOPT_HTTPHEADER,
//                    array('Content-Type: application/json',
//                        'Content-Length: ' . strlen($data_string))
//                );
//                $result = curl_exec($ch);
//                curl_close($ch);
// to debug
// if(curl_errno($ch)){
//     echo 'Curl error: ' . curl_error($ch);
// }

////                $code = rand(100000, 999999);
////                $new_verify->update([
////                    'otp_code' => $code,
////                ]);
///
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


    public function check_otp(CheckotpRequest $request): JsonResponse
    {
        $verify = Verify::where('phone',$request->phone_number)->first();

        if ($verify->otp_code == $request->otp_code) {

            $customer = Customer::create([
                'phone_number' => $request->phone_number,
            ]);

            $verify->update([
                'customer_id'=>$customer->id,
            ]);
            $profile_status=$customer->profile_status;

            $data = [
                'customer_id' => $customer->id,
                'phone' => $customer->phone_number,
                'profile_status' => $profile_status ? true : false,
                'token' => $customer->createToken('auth-token', ['*'], now()->addDay())->plainTextToken,
            ];
            $status = 200;
            $message = 'check otp Verified successfully';
            $isSuccess =true;
            $errors = null;

            return response_json($data,$status,$message,$isSuccess,$errors);

        } else {
            $data = [];
            $status = 200;
            $message = 'OTP is not valid';
            $isSuccess =false;
            $errors = ['message' => 'OTP is not valid'];

            return response_json($data,$status,$message,$isSuccess,$errors);

        }
    }
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
