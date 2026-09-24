# Store Tests: Failures Deactivation Guards

Pest POST store: Required active initial status and active dependent records block deactivation through separate mocked action guards.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Maps a required active initial record rejection to validation — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\WorkOrderStatuses\DeactivateWorkOrderStatus;
use App\Exceptions\CannotDeactivateWorkOrderStatus;
use App\Models\WorkOrderStatus;

describe('store', function (): void {
    it('maps a required active initial record rejection to validation', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->createOne();

        login(team: $workOrderStatus->team);

        mock(DeactivateWorkOrderStatus::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (WorkOrderStatus $workOrderStatusArgument): bool => $workOrderStatusArgument->is($workOrderStatus))
            ->andThrow(CannotDeactivateWorkOrderStatus::becauseItIsActiveInitial());

        $response = post(route('teams.work-order-statuses.deactivation.store', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]));

        $response->assertRedirectBackWithErrors([
            'work_order_status' => 'The team must keep an active initial work order status.',
        ]);
    });
});
```

## Maps an active dependent record rejection to validation — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\ServicePlans\DeactivateServicePlan;
use App\Exceptions\CannotDeactivateServicePlan;
use App\Models\ServicePlan;

describe('store', function (): void {
    it('maps an active dependent record rejection to validation', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();

        login(team: $servicePlan->team);

        mock(DeactivateServicePlan::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument): bool => $servicePlanArgument->is($servicePlan))
            ->andThrow(CannotDeactivateServicePlan::becauseItHasActiveCabinets());

        $response = post(route('teams.service-plans.deactivation.store', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]));

        $response->assertRedirectBackWithErrors([
            'service_plan' => 'This service plan has active cabinets and cannot be deactivated.',
        ]);
    });
});
```
