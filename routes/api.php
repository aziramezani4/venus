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

Route::post('register/{verify}', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('check/otp/{verify}', [AuthController::class, 'check_otp'])->name('check_otp');
Route::post('check/password/{account}', [AuthController::class, 'check_password'])->name('check_password');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
