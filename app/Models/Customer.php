<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model
{
    use HasApiTokens,HasFactory, SoftDeletes;

    protected $fillable = ['phone_number', 'user_name','password', 'first_name', 'last_name','profile_status','last_signin'];
}
