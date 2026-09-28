# Routes: Scope Web Endpoints

In `routes/web.php`, keep authenticated and verified scopes around nested resources. Scope model bindings through the parent; protect health results with the admin middleware. These examples show each route shape once: full or restricted resources, creatable singletons, named controller methods and invokable mutations.

The web `/up` route replaces the framework health response and returns 503 unless all package results are OK. Unless checks are paused, it runs fresh checks synchronously when requested with `fresh` or when `health.oh_dear_endpoint.always_send_fresh_results` is true (the package default).

```php
<?php

declare(strict_types=1);

use App\Http\Controllers\CabinetDeactivationController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\EnrollmentStatusController;
use App\Http\Controllers\InitialWorkOrderStatusController;
use App\Http\Controllers\MemberAddressController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MoveItemGroupController;
use App\Http\Controllers\PlanRateController;
use App\Http\Controllers\PlanRuleController;
use App\Http\Controllers\ServicePlanController;
use App\Http\Controllers\SetDefaultMemberAddressController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\WorkOrderLineController;
use Illuminate\Support\Facades\Route;
use Spatie\Health\Http\Controllers\HealthCheckResultsController;
use Spatie\Health\Http\Controllers\SimpleHealthCheckController;

Route::get('up', SimpleHealthCheckController::class);

Route::middleware('auth')->group(function (): void {
    Route::middleware('verified')->scopeBindings()->group(function (): void {
        Route::middleware('admin')->group(function (): void {
            Route::get('health', HealthCheckResultsController::class);
        });

        Route::get('teams/{team}/settings/general', [TeamController::class, 'show'])
            ->name('teams.settings.general');

        Route::resource('teams', TeamController::class)->only(['store', 'update']);
        Route::resource('teams.members', MemberController::class);
        Route::resource('teams.members.addresses', MemberAddressController::class);

        Route::singleton(
            'teams.members.cabinets.deactivation',
            CabinetDeactivationController::class
        )
            ->creatable()
            ->only(['destroy', 'store']);

        Route::patch(
            'teams/{team}/enrollments/{enrollment}/status',
            [EnrollmentStatusController::class, 'update']
        )->name('teams.enrollments.status.update');

        Route::resource('teams.enrollments', EnrollmentController::class)->only(['index']);

        Route::patch(
            'teams/{team}/members/{member}/addresses/{address}/default',
            SetDefaultMemberAddressController::class
        )->name('teams.members.addresses.make-default');

        Route::patch('teams/{team}/item-groups/{item_group}/move', MoveItemGroupController::class)
            ->name('teams.item-groups.move');

        Route::post(
            'teams/{team}/work-order-statuses/{work_order_status}/initial',
            [InitialWorkOrderStatusController::class, 'store']
        )->name('teams.work-order-statuses.initial.store');

        Route::resource('teams.work-orders', WorkOrderController::class);
        Route::resource('teams.work-orders.lines', WorkOrderLineController::class)
            ->except(['index']);

        Route::resource('teams.service-plans', ServicePlanController::class);
        Route::resource('teams.service-plans.plan-rules', PlanRuleController::class);
        Route::resource('teams.service-plans.plan-rules.rates', PlanRateController::class);
    });
});
```
