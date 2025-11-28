<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::middleware('web')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected routes
    Route::middleware('auth:web')->group(function () {
        // Auth routes
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        
        // Driver routes
        Route::apiResource('drivers', DriverController::class);
        
        // Trip routes
        Route::apiResource('trips', TripController::class);
        Route::get('/trips-report', [TripController::class, 'report']);
        Route::get('/trips-report/download', [TripController::class, 'downloadReport']);
        
        // User management routes (admin only)
        Route::middleware(\App\Http\Middleware\EnsureUserIsAdmin::class)->group(function () {
            Route::apiResource('users', UserController::class);
            Route::post('/register', [AuthController::class, 'register']);
        });
    });
});

