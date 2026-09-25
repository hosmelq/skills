# Action Tests: Create Role-Specific Relation Guards

Integration action tests: Reject foreign-tenant, inactive and trashed selections independently for current, received and pickup roles.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseCount;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Exceptions\WorkOrders\CurrentFacilityIsUnavailable;
use App\Exceptions\WorkOrders\PickupFacilityIsUnavailable;
use App\Exceptions\WorkOrders\ReceivedFacilityIsUnavailable;
use App\Models\Facility;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;

it('rejects an unavailable current relation', function (string $state): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $facility = match ($state) {
        'another team' => Facility::factory()->createOne(),
        'deactivated' => Facility::factory()->deactivated()->recycle($team)->createOne(),
        'soft deleted' => Facility::factory()->trashed()->recycle($team)->createOne(),
    };

    expect(fn () => resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'current_facility_id' => $facility->id,
    ])))->toThrow(
        CurrentFacilityIsUnavailable::class,
        'The selected current facility is unavailable.',
    );

    assertDatabaseCount(WorkOrder::class, 0);
})->with([
    'another team',
    'deactivated',
    'soft deleted',
]);

it('rejects an unavailable received relation', function (string $state): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $facility = match ($state) {
        'another team' => Facility::factory()->createOne(),
        'deactivated' => Facility::factory()->deactivated()->recycle($team)->createOne(),
        'soft deleted' => Facility::factory()->trashed()->recycle($team)->createOne(),
    };

    expect(fn () => resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'received_facility_id' => $facility->id,
    ])))->toThrow(
        ReceivedFacilityIsUnavailable::class,
        'The selected received facility is unavailable.',
    );

    assertDatabaseCount(WorkOrder::class, 0);
})->with([
    'another team',
    'deactivated',
    'soft deleted',
]);

it('rejects an unavailable pickup relation', function (string $state): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $facility = match ($state) {
        'another team' => Facility::factory()->createOne(),
        'deactivated' => Facility::factory()->deactivated()->recycle($team)->createOne(),
        'soft deleted' => Facility::factory()->trashed()->recycle($team)->createOne(),
    };

    expect(fn () => resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'pickup_facility_id' => $facility->id,
    ])))->toThrow(
        PickupFacilityIsUnavailable::class,
        'The selected pickup facility is unavailable.',
    );

    assertDatabaseCount(WorkOrder::class, 0);
})->with([
    'another team',
    'deactivated',
    'soft deleted',
]);
```
