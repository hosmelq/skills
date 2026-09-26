# Controllers: Map Deactivation Failures

Initial-state and active-dependent guards map to their own record fields. Reactivation delegates directly; an empty-body FormRequest is retained where used.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\WorkOrderStatuses\DeactivateWorkOrderStatus;
use App\Actions\WorkOrderStatuses\ReactivateWorkOrderStatus;
use App\Exceptions\CannotDeactivateWorkOrderStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;

class WorkOrderStatusDeactivationController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:deactivate,work_order_status', only: ['store']),
            new Middleware('can:reactivate,work_order_status', only: ['destroy']),
        ];
    }

    public function destroy(
        Team $team,
        WorkOrderStatus $workOrderStatus,
        ReactivateWorkOrderStatus $reactivateWorkOrderStatus,
    ): RedirectResponse {
        $reactivateWorkOrderStatus->handle($workOrderStatus);

        return back()->toast(__('work_order_status.reactivated.title'));
    }

    public function store(
        Team $team,
        WorkOrderStatus $workOrderStatus,
        DeactivateWorkOrderStatus $deactivateWorkOrderStatus,
    ): RedirectResponse {
        try {
            $deactivateWorkOrderStatus->handle($workOrderStatus);
        } catch (CannotDeactivateWorkOrderStatus) {
            throw ValidationException::withMessages([
                'work_order_status' => __('work_order_status.validation.initial_status_required'),
            ]);
        }

        return back()->toast(__('work_order_status.deactivated.title'));
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\ServicePlans\DeactivateServicePlan;
use App\Actions\ServicePlans\ReactivateServicePlan;
use App\Exceptions\CannotDeactivateServicePlan;
use App\Http\Requests\StoreServicePlanDeactivationRequest;
use App\Models\ServicePlan;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;

class ServicePlanDeactivationController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:deactivate,service_plan', only: ['store']),
            new Middleware('can:reactivate,service_plan', only: ['destroy']),
        ];
    }

    public function destroy(
        Team $team,
        ServicePlan $servicePlan,
        ReactivateServicePlan $reactivateServicePlan,
    ): RedirectResponse {
        $reactivateServicePlan->handle($servicePlan);

        return back()->toast(__('service_plan.reactivated.title'));
    }

    public function store(
        StoreServicePlanDeactivationRequest $request,
        Team $team,
        ServicePlan $servicePlan,
        DeactivateServicePlan $deactivateServicePlan,
    ): RedirectResponse {
        try {
            $deactivateServicePlan->handle($servicePlan);
        } catch (CannotDeactivateServicePlan) {
            throw ValidationException::withMessages([
                'service_plan' => __('service_plan.validation.active_cabinets'),
            ]);
        }

        return back()->toast(__('service_plan.deactivated.title'));
    }
}
```
