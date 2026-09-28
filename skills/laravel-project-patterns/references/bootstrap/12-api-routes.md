# Routes: Scope API Endpoints

In `routes/api.php`, keep login endpoints public with named throttles. Require Sanctum authentication for the user endpoint and add verification for scoped resources. Explicit `sqid` binding belongs to the resource route; it differs from decoding request-body identifiers.

```php
<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AppleAuthenticatedSessionController;
use App\Http\Controllers\Api\AuthenticatedUserController;
use App\Http\Controllers\Api\EmailAuthenticatedSessionController;
use App\Http\Controllers\Api\EmailOtpRequestController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\GoogleAuthenticatedSessionController;
use App\Http\Controllers\Api\TeamController;
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
        Route::middleware('verified')->group(function (): void {
            Route::apiResource('teams', TeamController::class)
                ->only(['show'])
                ->scoped(['team' => 'sqid']);

            Route::apiResource('teams.enrollments', EnrollmentController::class)
                ->only(['store'])
                ->scoped(['team' => 'sqid']);
        });

        Route::get('user', [AuthenticatedUserController::class, 'show'])
            ->name('user.show');
    });
});
```

See the [Sqid model identity](../models/10-sqid-identity.md).
