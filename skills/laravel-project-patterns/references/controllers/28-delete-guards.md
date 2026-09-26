# Controllers: Translate Delete Guards

Preserve history-use versus initial-state errors. A typed empty-body Request can remain part of the method contract even when the method reads no fields. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\Members\DeleteMember;
use App\Exceptions\CannotDeleteMemberWithWorkOrderHistory;
use App\Models\Member;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class MemberController
{
    public function destroy(
        Team $team,
        Member $member,
        DeleteMember $deleteMember,
    ): RedirectResponse {
        try {
            $deleteMember->handle($member);
        } catch (CannotDeleteMemberWithWorkOrderHistory) {
            throw ValidationException::withMessages([
                'member' => __('member.validation.in_use'),
            ]);
        }

        return to_route('teams.members.index', [
            'team' => $team,
        ])->toast(__('member.deleted.title'));
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\WorkOrderStatuses\DeleteWorkOrderStatus;
use App\Exceptions\CannotDeleteInitialWorkOrderStatus;
use App\Exceptions\CannotDeleteWorkOrderStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class WorkOrderStatusController
{
    public function destroy(
        Team $team,
        WorkOrderStatus $workOrderStatus,
        DeleteWorkOrderStatus $deleteWorkOrderStatus,
    ): RedirectResponse {
        try {
            $deleteWorkOrderStatus->handle($workOrderStatus);
        } catch (CannotDeleteInitialWorkOrderStatus) {
            throw ValidationException::withMessages([
                'work_order_status' => __('work_order_status.validation.initial_status_required'),
            ]);
        } catch (CannotDeleteWorkOrderStatus) {
            throw ValidationException::withMessages([
                'work_order_status' => __('work_order_status.validation.in_use'),
            ]);
        }

        return to_route('teams.work-order-statuses.index', [
            'team' => $team,
        ])->toast(__('work_order_status.deleted.title'));
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\ServicePlans\DeleteServicePlan;
use App\Exceptions\CannotDeleteServicePlanInUse;
use App\Http\Requests\DestroyServicePlanRequest;
use App\Models\ServicePlan;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class ServicePlanController
{
    public function destroy(
        DestroyServicePlanRequest $request,
        Team $team,
        ServicePlan $servicePlan,
        DeleteServicePlan $deleteServicePlan
    ): RedirectResponse {
        try {
            $deleteServicePlan->handle($servicePlan);
        } catch (CannotDeleteServicePlanInUse) {
            throw ValidationException::withMessages([
                'service_plan' => __('service_plan.validation.in_use'),
            ]);
        }

        return to_route('teams.service-plans.index', [
            'team' => $team,
        ])->toast(__('service_plan.deleted.title'));
    }
}
```
