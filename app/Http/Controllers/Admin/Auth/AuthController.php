<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Account\AccountResource;
use App\Http\Resources\Verify\VerifyResource;
use App\Models\Account;
use App\Models\Verify;
use Illuminate\Support\Facades\Hash;
use Melipayamak;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse|AnonymousResourceCollection
    {
        try {
                $verify=Verify::where('phone',$request->phone)->first();
                if(!$verify) {
                    $new_verify = Verify::create([
                        'phone' => $request->phone
                    ]);
//                $new_verify->sendOtp();

                    try{
                        $sms = Melipayamak::sms();
                        $code = rand(100000, 999999);
                        $to = $request->phone;
                        $from = '5000...';
                        $response = $sms->send($to,$from,$code);
                        $json = json_decode($response);
                        echo $json->Value; //RecId or Error Number
                    }catch(Exception $e){
                        echo $e->getMessage();
                    }

                    return response()->json([
                        'data' => $new_verify,
                        'message' => 'login successfully',
                    ], 200);
                }else {
                    $user=Account::where('phone',$request->phone)->first();
                    $verify->update([
                        'account_id' => $user->id,
                    ]);
                    return response()->json([
                        'data' => $verify,
                        'message' => 'login successfully',
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
    public function check_otp(Verify $verify, Request $request): JsonResponse
    {
        if ($verify->otp_code == $request->otp_code) {

//            $account->updateUserVerified(true, null);
//            $account->tokens()->delete();

            $response = [
                'data' => $verify,
                'token' => $verify->createToken('auth-token', ['*'], now()->addDay())->plainTextToken,
                'status' => 200,
                'message' => 'Account Verified successfully',
                'isSuccess' => true,
                'errors' => null,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json(['data' => [], 'status' => 200, 'message' => 'OTP is not valid', 'isSuccess' => false, 'errors' => ['message' => 'OTP is not valid',]], 200);

        }
    }
    public function check_password(Account $account, Request $request): JsonResponse
    {
        if ($account->password == $request->password) {

//            $account->updateUserVerified(true, null);
//            $account->tokens()->delete();

            $response = [
                'data' => $account,
                'token' => $account->createToken('auth-token', ['*'], now()->addDay())->plainTextToken,
                'status' => 200,
                'message' => 'Account Verified successfully',
                'isSuccess' => true,
                'errors' => null,
            ];

            return response()->json($response, 200);
        } else {
            return response()->json(['data' => [], 'status' => 200, 'message' => 'OTP is not valid', 'isSuccess' => false, 'errors' => ['message' => 'OTP is not valid',]], 200);

        }
    }
    public function register(Verify $verify,RegisterRequest $request): Json|AccountResource
    {
        try {
            $user = Account::create([
                'username' => $request->username,
                'phone' => $verify->phone,
                'national_code' => $request->national_code,
                'password' => Hash::make($request->password),
               ]);
            return new AccountResource($user);
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
