# Action Tests: Update New Relation Guards

Integration action tests: Reject newly assigned unavailable assignments, pickup and received roles, and plans across foreign-tenant, inactive and trashed cases; retain the old null value.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Exceptions\WorkOrders\CabinetIsUnavailable;
use App\Exceptions\WorkOrders\PickupFacilityIsUnavailable;
use App\Exceptions\WorkOrders\ReceivedFacilityIsUnavailable;
use App\Exceptions\WorkOrders\ServicePlanIsUnavailable;
use App\Models\Cabinet;
use App\Models\Facility;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;

it('rejects a newly assigned unavailable assignment', function (string $state): void {
    $team = Team::factory()->createOne();
    $workOrder = WorkOrder::factory()->for($team)->createOne();
    $cabinet = match ($state) {
        'another team' => Cabinet::factory()->createOne(),
        'deactivated' => Cabinet::factory()->deactivated()->recycle($team)->createOne(),
        'soft deleted' => Cabinet::factory()->trashed()->recycle($team)->createOne(),
    };

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'cabinet_id' => $cabinet->id,
    ])))->toThrow(
        CabinetIsUnavailable::class,
        'The selected cabinet is unavailable.',
    );

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'cabinet_id' => null,
    ]);
})->with([
    'another team',
    'deactivated',
    'soft deleted',
]);

it('rejects a newly assigned unavailable pickup relation', function (string $state): void {
    $team = Team::factory()->createOne();
    $workOrder = WorkOrder::factory()->for($team)->createOne();
    $facility = match ($state) {
        'another team' => Facility::factory()->createOne(),
        'deactivated' => Facility::factory()->deactivated()->recycle($team)->createOne(),
        'soft deleted' => Facility::factory()->trashed()->recycle($team)->createOne(),
    };

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'pickup_facility_id' => $facility->id,
    ])))->toThrow(
        PickupFacilityIsUnavailable::class,
        'The selected pickup facility is unavailable.',
    );

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'pickup_facility_id' => null,
    ]);
})->with([
    'another team',
    'deactivated',
    'soft deleted',
]);

it('rejects a newly assigned unavailable received relation', function (string $state): void {
    $team = Team::factory()->createOne();
    $workOrder = WorkOrder::factory()->for($team)->createOne();
    $facility = match ($state) {
        'another team' => Facility::factory()->createOne(),
        'deactivated' => Facility::factory()->deactivated()->recycle($team)->createOne(),
        'soft deleted' => Facility::factory()->trashed()->recycle($team)->createOne(),
    };

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'received_facility_id' => $facility->id,
    ])))->toThrow(
        ReceivedFacilityIsUnavailable::class,
        'The selected received facility is unavailable.',
    );

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'received_facility_id' => null,
    ]);
})->with([
    'another team',
    'deactivated',
    'soft deleted',
]);

it('rejects a newly assigned unavailable plan', function (string $state): void {
    $team = Team::factory()->createOne();
    $workOrder = WorkOrder::factory()->for($team)->createOne();
    $servicePlan = match ($state) {
        'another team' => ServicePlan::factory()->createOne(),
        'deactivated' => ServicePlan::factory()->deactivated()->recycle($team)->createOne(),
        'soft deleted' => ServicePlan::factory()->trashed()->recycle($team)->createOne(),
    };

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'service_plan_id' => $servicePlan->id,
    ])))->toThrow(
        ServicePlanIsUnavailable::class,
        'The selected service plan is unavailable.',
    );

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'service_plan_id' => null,
    ]);
})->with([
    'another team',
    'deactivated',
    'soft deleted',
]);
```
