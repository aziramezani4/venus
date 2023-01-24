<?php

namespace App\Models;

use App\Facades\SMSGateway;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;

class Account extends Model
{
    use HasApiTokens,HasFactory, SoftDeletes;

    protected $fillable = ['username', 'type','phone', 'national_code', 'password'];

}
