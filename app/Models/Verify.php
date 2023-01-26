<?php

namespace App\Models;

use App\Facades\SMSGateway;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Verify extends Model
{
    use HasApiTokens,HasFactory, SoftDeletes;

    protected $fillable = ['phone', 'otp_code','account_id'];

//    public function sendOtp(): void
//    {
//
//        try {
//            $code = rand(100000, 999999);
//            $this->update(['otp_code' => $code]);
//            SMSGateway::sendSMS($this->phone, $code);
//        } catch (Exception $e) {
//            Log::error($e->getMessage());
//            echo $e->getMessage();
//        }
//    }

}
