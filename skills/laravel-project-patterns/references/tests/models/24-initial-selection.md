# Model Tests: Initial State Constraints

Database initial-state rules: one active initial per tenant, a required base state, and replacement after deactivation. Initial selection and name reservation are different constraints.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Enums\BaseStatus;
use App\Models\WorkOrderStatus;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;

it('enforces one active initial state per tenant at the database level', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->initial()->createOne();

    expect(fn () => WorkOrderStatus::factory()->initial()->recycle($workOrderStatus->team)->createOne([
        'name' => 'Second initial',
    ]))->toThrow(function (UniqueConstraintViolationException $exception): void {
        expect($exception->index)->toBe('work_order_statuses_active_initial_unique');
    });
});

it('requires the eligible base state for an initial record at the database level', function (): void {
    expect(fn () => WorkOrderStatus::factory()->createOne([
        'base_status' => BaseStatus::InProgress,
        'is_initial' => true,
    ]))->toThrow(QueryException::class, 'work_order_statuses_initial_base_status_check');
});

it('allows another initial state after deactivation', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->initial()->deactivated()->createOne();

    $replacement = WorkOrderStatus::factory()->initial()->recycle($workOrderStatus->team)->createOne([
        'name' => 'Second initial',
    ]);

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $replacement->id,
        'is_initial' => true,
    ]);
});
```
