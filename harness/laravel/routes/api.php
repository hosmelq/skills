<?php

use App\Http\Controllers\Api\AppleAuthenticatedSessionController;
use App\Http\Controllers\Api\AuthenticatedUserController;
use App\Http\Controllers\Api\EmailAuthenticatedSessionController;
use App\Http\Controllers\Api\EmailOtpRequestController;
use App\Http\Controllers\Api\GoogleAuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function (): void {
    Route::name('auth.')->prefix('auth')->group(function (): void {
        Route::post('apple', AppleAuthenticatedSessionController::class)
            ->middleware(['throttle:apple.login'])
            ->name('apple.login');

        Route::post('google', GoogleAuthenticatedSessionController::class)
            ->middleware(['throttle:google.login'])
            ->name('google.login');

        Route::post('email/request', EmailOtpRequestController::class)
            ->middleware(['throttle:email.request'])
            ->name('email.request');
        Route::post('email/login', EmailAuthenticatedSessionController::class)
            ->middleware(['throttle:email.login'])
            ->name('email.login');
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('user', [AuthenticatedUserController::class, 'show'])
            ->name('user.show');
    });
});
