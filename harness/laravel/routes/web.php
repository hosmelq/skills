<?php

use App\Http\Controllers\ClientAddressController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MoveVisitStatusController;
use App\Http\Controllers\RoomDeactivationController;
use App\Http\Controllers\VisitStatusController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');

Route::get('/login', fn (): Response => Inertia::render('auth/Login'))
    ->middleware('guest')
    ->name('login');

Route::middleware('auth')->scopeBindings()->group(function (): void {
    Route::resource('clinics', ClinicController::class)->only(['update']);

    Route::resource('clinics.clients', ClientController::class);
    Route::resource('clinics.clients.addresses', ClientAddressController::class)->only(['store']);

    Route::singleton('clinics.rooms.deactivation', RoomDeactivationController::class)
        ->creatable()
        ->only(['destroy', 'store']);

    Route::patch('/clinics/{clinic}/visit-statuses/{visit_status}/move', MoveVisitStatusController::class)
        ->name('clinics.visit-statuses.move');

    Route::resource('clinics.visit-statuses', VisitStatusController::class)->only(['update']);
});
