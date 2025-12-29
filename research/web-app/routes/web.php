<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\OrganController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MobileUserController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\DetectionController;
use App\Http\Controllers\DetectionSessionController;
use App\Http\Controllers\AnalyticsController;


use App\Http\Controllers\LanguageController;

Route::get('/language/{locale}', [LanguageController::class, 'switchLanguage'])->name('language.switch');

Route::get('/', [AuthenticationController::class, 'index'])->name("index");
Route::post('/login', [AuthenticationController::class, 'login'])->name("login");
Route::get('/logout', [AuthenticationController::class, 'logout'])->name("logout");


Route::middleware(['CheckAdminAuth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'showDashboard'])->name("show.dashboard");
    Route::get('/organs', [OrganController::class, 'showOrgans'])->name("show.organs");
    Route::get('/organs/requests', [OrganController::class, 'showOrganRequests'])->name("show.organ.requests");
    Route::get('/messages', [OrganController::class, 'showMessages'])->name("show.messages");
    Route::get('/settings', [SettingController::class, 'showSettings'])->name("show.settings");

    Route::get('/users/mobile', [MobileUserController::class, 'showMobileUsers'])->name("show.mobile.users");
    Route::post('/users/mobile/ajax',  [MobileUserController::class, 'processMobileUsersAjax'])->name('process.mobile.users.ajax');
    Route::post('/users/mobile/approve/{id}', [MobileUserController::class, 'approveUser'])->name("approve.user");
    Route::delete('/users/delete/{id}', [MobileUserController::class, 'deleteUser'])->name('delete.user');
    
    Route::post('process/messages/ajax', [OrganController::class, 'processMessagesAjax'])->name('process.messages.ajax');

    Route::post('/organs/add', [OrganController::class, 'addOrgan'])->name('add.organ');
    Route::post('/organs/ajax',  [OrganController::class, 'processOrgansAjax'])->name('process.organs.ajax');
    Route::delete('/organs/delete/{id}', [OrganController::class, 'deleteOrgan'])->name('delete.organ');
    Route::post('organ-requests/accept/{id}', [OrganController::class, 'acceptOrganRequest'])->name('accept.organ.request');
    Route::post('organ-requests/reject/{id}', [OrganController::class, 'rejectOrganRequest'])->name('reject.organ.request');
    Route::post('organ-requests/ajax', [OrganController::class, 'processOrganRequestsAjax'])->name('process.organ.requests.ajax');

    // Elephant Detection Routes
    Route::prefix('detections')->name('detections.')->group(function () {
        Route::get('/', [DetectionController::class, 'index'])->name('index');
        Route::get('/{id}', [DetectionController::class, 'show'])->name('show');
        Route::post('/ajax', [DetectionController::class, 'ajax'])->name('ajax');
    });

    // Detection Sessions Routes
    Route::prefix('sessions')->name('sessions.')->group(function () {
        Route::get('/', [DetectionSessionController::class, 'index'])->name('index');
        Route::get('/{id}', [DetectionSessionController::class, 'show'])->name('show');
        Route::delete('/{id}', [DetectionSessionController::class, 'destroy'])->name('destroy');
        Route::post('/ajax', [DetectionSessionController::class, 'ajax'])->name('ajax');
    });

    // Analytics Routes
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/behavior-chart', [AnalyticsController::class, 'getBehaviorChart'])->name('behavior.chart');
        Route::get('/speed-chart', [AnalyticsController::class, 'getSpeedChart'])->name('speed.chart');
        Route::get('/timeline-chart', [AnalyticsController::class, 'getTimelineChart'])->name('timeline.chart');
        Route::get('/alert-chart', [AnalyticsController::class, 'getAlertChart'])->name('alert.chart');
        Route::get('/sessions-chart', [AnalyticsController::class, 'getSessionsChart'])->name('sessions.chart');
    });

    Route::post('/update-username', [SettingController::class, 'updateUsername'])->name('update.username');
    Route::post('/update-password', [SettingController::class, 'updatePassword'])->name('update.password');

});


