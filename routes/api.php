<?php

use App\Http\Controllers\Admin\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::redirect('/documentation', '/request-docs');
Route::post('customer/signup', [AuthController::class, 'signup'])->name('signup');
Route::post('customer/signup/check/otp', [AuthController::class, 'check_otp'])->name('check_otp');
Route::post('customer/check/password', [AuthController::class, 'check_password'])->name('check_password');
Route::post('customer/new/password/{verify}', [AuthController::class, 'new_password'])->name('new_password');
Route::post('customer/register/{verify}', [AuthController::class, 'register'])->name('register');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
