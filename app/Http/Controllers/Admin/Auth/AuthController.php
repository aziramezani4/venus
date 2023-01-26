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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;
use Melipayamak;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class AuthController extends Controller
{
    public function signup(LoginRequest $request): JsonResponse|AnonymousResourceCollection
    {

        try {
            $verify = Verify::where('phone', $request->phone)->first();
            if (!$verify) {
                $new_verify = Verify::create([
                    'phone' => $request->phone
                ]);
//                $new_verify->sendOtp();

//                try{
//                    $sms = Melipayamak::sms();
//                    $code = rand(100000, 999999);
//                    $to = $request->phone;
//                    $from = '5000...';
//                    $response = $sms->send($to,$from,$code);
//                    $json = json_decode($response);
//                    echo $json->Value; //RecId or Error Number
//                }catch(Exception $e){
//                    echo $e->getMessage();
//                }
                $code = rand(100000, 999999);
                $new_verify->update([
                    'otp_code' => $code,
                ]);
                return response()->json([
                    'data' => $new_verify->phone,
                    'message' => 'otp_code send successfully',
                ], 200);
            } else {
                $user = Customer::where('phone_number', $request->phone)->first();
                $verify->update([
                    'account_id' => $user->id,
                ]);
                $date = new DateTime();
                $user->update([
                    'last_login' => $date,
                ]);
                return response()->json([
                    'data' => $verify,
                    'message' => 'login successfully',
                ], 200);
            }
        } catch (Exception $e) {

                $response = [
                    'status' => 200,
                    'message' => 'shrg',
                    'isSuccess' => false,
//                    'errors' => [
////                        'message' => $e->getMessage(),
//                    ],
                ];
                return response()->json($response, 200);
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
            $response = [
                'data'=>[
                'customer_id' => $customer->id,
                'phone' => $customer->phone_number,
                'profile_status' => $profile_status ? true : false,
                'token' => $customer->createToken('auth-token', ['*'], now()->addDay())->plainTextToken,
                    ],
                'status' => 200,
                'message' => 'check otp Verified successfully',
                'isSuccess' => true,
                'errors' => null,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => 200,
                'message' => 'OTP is not valid',
                'isSuccess' => false,
                'errors' => ['message' => 'OTP is not valid',]
            ], 200);

        }
    }
    public function check_password(Request $request): JsonResponse
    {
        $customer = Customer::where('phone_number',$request->phone_number)->first();

        if (Hash::check($request->password,$customer->password)) {

            $response = [
                'data' => $customer,
                'token' => $customer->createToken('auth-token', ['*'], now()->addDay())->plainTextToken,
                'status' => 200,
                'message' => 'Account Verified successfully',
                'isSuccess' => true,
                'errors' => null,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json([
                'data' => [],
                'status' => 200,
                'message' => 'Password is not valid',
                'isSuccess' => false,
                'errors' => ['message' => 'Password is not valid',]
            ], 200);

        }
    }
    public function register(Verify $verify,RegisterRequest $request): JsonResponse|CustomerResource
    {
        try {
            $customer = Customer::create([
                'phone_number' => $verify->phone,
                'user_name' => $request->user_name,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'password' => Hash::make($request->password),
               ]);
            $verify->update([
               'customer_id'=>$customer->id,
            ]);
            return new CustomerResource($customer);
        } catch (Exception $e) {
            $response = [
                'status' => 200,
                'message' => 'Internal server error',
                'isSuccess' => false,
                'errors' => [
                    'message' => $e->getMessage(),
                ],
            ];
            return response()->json($response, 201);
        }

    }
    public function reset_password(LoginRequest $request): JsonResponse|AnonymousResourceCollection
    {
        try {
            $verify=Verify::where('phone',$request->phone)->first();
            if(!$verify) {

                return response()->json([
                    'data' => [],
                    'message' => 'Customer can not found',
                ], 200);

            }else {
//                try{
//                    $sms = Melipayamak::sms();
//                    $code = rand(100000, 999999);
//                    $to = $request->phone;
//                    $from = '5000...';
//                    $response = $sms->send($to,$from,$code);
//                    $json = json_decode($response);
//                    echo $json->Value; //RecId or Error Number
//                }catch(Exception $e){
//                    echo $e->getMessage();
//                }
                $code = rand(100000, 999999);
                $verify->update([
                    'otp_code' => $code,
                ]);
                return response()->json([
                    'data' => $verify,
                    'message' => 'otp_code send successfully',
                ], 200);
            }
        } catch (Exception $e) {
            $response = [
                'status' => 200,
                'message' => 'Internal server error',
                'isSuccess' => false,
                'errors' => [
                    'message' => $e->getMessage(),
                ],
            ];
            return response()->json($response, 200);
        }
    }
    public function new_password(Verify $verify,Request $request): JsonResponse|CustomerResource
    {
        $customer = Customer::where('phone_number',$verify->phone)->first();
        try {
        if($request->password == $request->re_password) {
           $customer->update([
            'password' => Hash::make($request->password),
           ]);
         return new CustomerResource($customer);
           }else{
         return response()->json([
           'status' => 200,
           'message' => 'Password Incorrect',
           'isSuccess' => false,
       ]);
}
        } catch (Exception $e) {
            $response = [
                'status' => 200,
                'message' => 'Internal server error',
                'isSuccess' => false,
                'errors' => [
                    'message' => $e->getMessage(),
                ],
            ];
            return response()->json($response, 201);
        }

    }
}
