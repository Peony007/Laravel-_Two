<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserAuthController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('cors')->group(function () {
    Route::post('/login',[UserAuthController::class, 'login'])->name('login.api');
    Route::post('/register',[UserAuthController::class, 'register'])->name('register.api');
    Route::get('/verify',[UserAuthController::class, 'verify'])->name('verify.api');
    Route::post('/resetpassword',[UserAuthController::class, 'sendresetpasswordemail'])->name('resetpasswordemail.api');
    Route::get('/resetpassword',[UserAuthController::class, 'resetpassword'])->name('resetpassword.api');
});
Route::middleware('auth:api')->group(function () {
    // our routes to be protected will go in here
    Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout.api');
    Route::post('/user', function(Request $request) {
        return $request->user();
    });
});

