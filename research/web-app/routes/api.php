<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AppController;

// Mobile Apps

Route::post('/login/mobile', [AuthController::class, 'login']);
Route::post('register/mobile', [AuthController::class, 'register']);
Route::get('/user/profile/mobile', [UserController::class, 'getProfile']);
Route::post('/user/change-password/mobile', [UserController::class, 'changePassword']);
Route::get('/organs/mobile', [AppController::class, 'getOrgans']);
Route::get('/organ/mobile/{id}', [AppController::class, 'getOrganById']);
Route::post('/request-organ/mobile', [AppController::class, 'requestOrgan']);
Route::post('/check-organ-request/mobile', [AppController::class, 'checkOrganRequest']);
Route::post('/organs/matching/mobile', [AppController::class, 'getMatchingOrgans']);
Route::post('/organ-requests/mobile', [AppController::class, 'getUserRequests']);
Route::post('/send-message/mobile', [AppController::class, 'sendMessage']);




