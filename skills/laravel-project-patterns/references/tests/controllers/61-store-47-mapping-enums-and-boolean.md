# Store Tests: Mapping Enums And Boolean

Pest POST store: Enum/name and separate string-to-boolean mapping; enum/integer/name/unit mapping retained as another complete example.

## Complete block — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\WorkOrderStatuses\CreateWorkOrderStatus;
use App\Actions\WorkOrderStatuses\Inputs\CreateWorkOrderStatusInput;
use App\Enums\WorkOrderBaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;

describe('store', function (): void {
    it('stores the record', function (): void {
        $team = Team::factory()->createOne();
        $workOrderStatus = WorkOrderStatus::factory()
            ->for($team)
            ->createOne();

        signIn(team: $team);

        mock(CreateWorkOrderStatus::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                Team $teamArgument,
                CreateWorkOrderStatusInput $input
            ): bool => $teamArgument->is($team)
                && $input->baseStatus === WorkOrderBaseStatus::ReadyForPickup
                && $input->name === 'Ready')
            ->andReturn($workOrderStatus);

        $response = post(route('teams.work-order-statuses.store', [
            'team' => $team,
        ]), [
            'base_status' => WorkOrderBaseStatus::ReadyForPickup(),
            'name' => 'Ready',
        ]);

        $response->assertRedirectToRoute('teams.work-order-statuses.show', [
            'team' => $team,
            'work_order_status' => $workOrderStatus,
        ])
            ->assertToast('Work order status created');
    });

    it('maps a boolean field into the input', function (): void {
        $team = Team::factory()->createOne();
        $workOrderStatus = WorkOrderStatus::factory()
            ->for($team)
            ->createOne();

        signIn(team: $team);

        mock(CreateWorkOrderStatus::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                Team $teamArgument,
                CreateWorkOrderStatusInput $input
            ): bool => $teamArgument->is($team)
                && $input->isMemberVisible === true)
            ->andReturn($workOrderStatus);

        $response = post(route('teams.work-order-statuses.store', [
            'team' => $team,
        ]), [
            'base_status' => WorkOrderBaseStatus::Received(),
            'is_member_visible' => '1',
            'name' => 'Member received',
        ]);

        $response->assertRedirectToRoute('teams.work-order-statuses.show', [
            'team' => $team,
            'work_order_status' => $workOrderStatus,
        ])
            ->assertToast('Work order status created');
    });
});
```

## Stores the record — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\ServicePlans\CreateServicePlan;
use App\Actions\ServicePlans\Inputs\CreateServicePlanInput;
use App\Enums\TransitTimeUnit;
use App\Enums\WeightUnit;
use App\Models\ServicePlan;
use App\Models\Team;

describe('store', function (): void {
    it('stores the record', function (): void {
        $team = Team::factory()->createOne();
        $servicePlan = ServicePlan::factory()
            ->for($team)
            ->createOne();

        signIn(team: $team);

        mock(CreateServicePlan::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Team $teamArgument, CreateServicePlanInput $input): bool => $teamArgument->is($team)
                && $input->estimatedTransitTimeUnit === TransitTimeUnit::Days
                && $input->minimumEstimatedTransitTime === 4
                && $input->name === 'Air Freight'
                && $input->weightUnit === WeightUnit::Pounds)
            ->andReturn($servicePlan);

        $response = post(route('teams.service-plans.store', [
            'team' => $team,
        ]), [
            'estimated_transit_time_unit' => TransitTimeUnit::Days(),
            'minimum_estimated_transit_time' => 4,
            'name' => 'Air Freight',
            'weight_unit' => WeightUnit::Pounds(),
        ]);

        $response->assertRedirectToRoute('teams.service-plans.show', [
            'team' => $team,
            'service_plan' => $servicePlan,
        ])
            ->assertToast('Service plan created');
    });
});
```
