# Action Tests: Create Reference Uniqueness

Integration action tests: Reject a case-insensitive active reference collision and allow reuse of a reference held only by a soft-deleted record.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Exceptions\WorkOrders\WorkOrderReferenceAlreadyExists;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;

it('rejects a case-insensitive active reference collision', function (): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    WorkOrder::factory()->for($team)->createOne(['reference' => 'REC-ABC']);

    expect(fn () => resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'reference' => 'rec-abc',
    ])))->toThrow(
        WorkOrderReferenceAlreadyExists::class,
        'The work order reference already exists.',
    );
});

it('reuses a reference from a soft-deleted record', function (): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    WorkOrder::factory()->trashed()->for($team)->createOne(['reference' => 'REC-ABC']);

    $workOrder = resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'reference' => 'rec-abc',
    ]));

    assertDatabaseHas(WorkOrder::class, ['id' => $workOrder->id, 'reference' => 'rec-abc']);
});
```
