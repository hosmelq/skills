# Action Tests: Select a Tenant Initial State

Integration tests for selecting an initial state: enforce candidate eligibility, clear competing flags including inactive and soft-deleted rows within the tenant, and assert the returned record.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrderStatuses\SetInitialWorkOrderStatus;
use App\Enums\BaseStatus;
use App\Exceptions\CannotSetInitialWorkOrderStatus;
use App\Models\WorkOrderStatus;

it('rejects deactivated statuses', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->deactivated()->createOne([
        'base_status' => BaseStatus::Open,
    ]);

    expect(fn () => resolve(SetInitialWorkOrderStatus::class)->handle($workOrderStatus))
        ->toThrow(
            CannotSetInitialWorkOrderStatus::class,
            'Only an active open work order status can be initial.',
        );

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'is_initial' => false,
    ]);
});

it('rejects ineligible statuses', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->createOne([
        'base_status' => BaseStatus::InProgress,
    ]);

    expect(fn () => resolve(SetInitialWorkOrderStatus::class)->handle($workOrderStatus))
        ->toThrow(
            CannotSetInitialWorkOrderStatus::class,
            'Only an active open work order status can be initial.',
        );

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'is_initial' => false,
    ]);
});

it('sets an active open status as the only initial status', function (): void {
    $initialWorkOrderStatus = WorkOrderStatus::factory()->initial()->createOne();

    $newInitialWorkOrderStatus = WorkOrderStatus::factory()
        ->recycle($initialWorkOrderStatus->team)
        ->createOne();

    $resolvedWorkOrderStatus = resolve(SetInitialWorkOrderStatus::class)->handle($newInitialWorkOrderStatus);

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $initialWorkOrderStatus->id,
        'is_initial' => false,
    ]);
    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $newInitialWorkOrderStatus->id,
        'is_initial' => true,
    ]);

    expect($resolvedWorkOrderStatus->is($newInitialWorkOrderStatus))->toBeTrue();
});

it('clears deactivated and soft deleted initial statuses when setting a new initial status', function (): void {
    $deactivatedInitialWorkOrderStatus = WorkOrderStatus::factory()
        ->initial()
        ->deactivated()
        ->createOne();

    $softDeletedInitialWorkOrderStatus = WorkOrderStatus::factory()
        ->initial()
        ->trashed()
        ->recycle($deactivatedInitialWorkOrderStatus->team)
        ->createOne();

    $newInitialWorkOrderStatus = WorkOrderStatus::factory()
        ->recycle($deactivatedInitialWorkOrderStatus->team)
        ->createOne();

    $resolvedWorkOrderStatus = resolve(SetInitialWorkOrderStatus::class)->handle($newInitialWorkOrderStatus);

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $deactivatedInitialWorkOrderStatus->id,
        'is_initial' => false,
    ]);
    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $softDeletedInitialWorkOrderStatus->id,
        'is_initial' => false,
    ]);
    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $newInitialWorkOrderStatus->id,
        'is_initial' => true,
    ]);

    expect($resolvedWorkOrderStatus->is($newInitialWorkOrderStatus))->toBeTrue();
});
```
