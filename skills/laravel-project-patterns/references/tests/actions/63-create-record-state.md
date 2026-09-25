# Action Tests: Create State Guards

Integration action tests: Reject a missing eligible initial state without repairing it, and reject explicit inactive, ineligible, foreign-tenant or trashed states.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseCount;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Enums\BaseStatus;
use App\Exceptions\WorkOrders\InitialWorkOrderStatusIsUnavailable;
use App\Exceptions\WorkOrders\WorkOrderStatusIsUnavailable;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;

it('rejects a missing initial state without repairing it', function (): void {
    $team = Team::factory()->createOne();

    expect(fn () => resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([])))
        ->toThrow(
            InitialWorkOrderStatusIsUnavailable::class,
            'An active initial received work order status is required.',
        );

    assertDatabaseCount(WorkOrderStatus::class, 0);
    assertDatabaseCount(WorkOrder::class, 0);
});

it('rejects an unavailable status', function (string $state): void {
    $team = Team::factory()->createOne();
    $status = match ($state) {
        'deactivated' => WorkOrderStatus::factory()->deactivated()->recycle($team)->createOne(),
        'non received' => WorkOrderStatus::factory()
            ->withBaseStatus(BaseStatus::InProgress)
            ->recycle($team)
            ->createOne(),
        'other team' => WorkOrderStatus::factory()->createOne(),
        'soft deleted' => WorkOrderStatus::factory()->trashed()->recycle($team)->createOne(),
    };

    expect(fn () => resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'work_order_status_id' => $status->id,
    ])))->toThrow(
        WorkOrderStatusIsUnavailable::class,
        'The selected work order status is unavailable.',
    );

    assertDatabaseCount(WorkOrder::class, 0);
})->with([
    'deactivated',
    'non received',
    'other team',
    'soft deleted',
]);
```
