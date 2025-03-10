<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::prefix('/user')->group(function(){
        Route::get('/', function (Request $request) {
            return $request->user();
        });
        Route::post('/setup-profile', [UserController::class, 'setupProfile'])->name('setupProfile');
    });
});