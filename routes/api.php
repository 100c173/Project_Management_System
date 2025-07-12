<?php

use App\Http\Controllers\Api\V1\AuthenticationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function(){

    /**
     ** Authentication User Login/Register/Logout/Me
     */
    Route::post('/register',[AuthenticationController::class,'register'])->name('auth.register');
    Route::post('/login',[AuthenticationController::class,'login'])->middleware('throttle:5,1')->name('auth.login'); // 5 attempts per minute
    

    Route::middleware('auth:sanctum')->group(function(){
        Route::post('/logout',[AuthenticationController::class,'logout'])->name('auth.logout');
        Route::get('/user',[AuthenticationController::class,'user'])->name('auth.me');
    });

    /**
     * *****************************************************
     */

});