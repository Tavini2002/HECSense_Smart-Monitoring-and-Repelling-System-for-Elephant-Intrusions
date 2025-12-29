<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\DetectionController as APIDetectionController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DetectionSessionController;
use App\Http\Controllers\DetectionController;
use App\Http\Controllers\AlertController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Mobile Authentication Routes
Route::post('/register/mobile', [AuthController::class, 'register'])->name('api.register.mobile');
Route::post('/login/mobile', [AuthController::class, 'login'])->name('api.login.mobile');

// Mobile User Routes
Route::prefix('user')->name('api.user.')->group(function () {
    Route::get('/profile', [UserController::class, 'getProfile'])->name('profile');
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('change-password');
});

// Elephant Detection API Routes
Route::prefix('detections')->name('api.detections.')->group(function () {
    // Session management
    Route::post('/sessions', [APIDetectionController::class, 'createSession'])->name('sessions.create');
    Route::put('/sessions/{id}', [APIDetectionController::class, 'updateSession'])->name('sessions.update');
    
    // Detection storage
    Route::post('/store', [APIDetectionController::class, 'storeDetection'])->name('store');
    Route::post('/store-batch', [APIDetectionController::class, 'storeDetectionsBatch'])->name('store.batch');
    
    // Alerts and zone transitions
    Route::post('/alerts', [APIDetectionController::class, 'storeAlert'])->name('alerts.store');
    Route::post('/zone-transitions', [APIDetectionController::class, 'storeZoneTransition'])->name('zone-transitions.store');
});

// Mobile Dashboard/Statistics Routes
Route::prefix('mobile')->name('api.mobile.')->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'getMobileDashboardStats'])->name('dashboard.stats');
    Route::get('/sessions', [DetectionSessionController::class, 'getMobileSessions'])->name('sessions');
    Route::get('/sessions/{id}', [DetectionSessionController::class, 'getMobileSession'])->name('sessions.show');
    Route::get('/detections', [DetectionController::class, 'getMobileDetections'])->name('detections');
    Route::get('/alerts', [AlertController::class, 'getMobileAlerts'])->name('alerts');
    Route::get('/alerts/recent-aggressive', [AlertController::class, 'getRecentAggressiveAlerts'])->name('alerts.recent-aggressive');
});
