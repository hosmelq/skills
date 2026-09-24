# Action Tests: Ensure an Initial State

Integration tests for ensuring one initial state: create when absent, return an existing selection, promote the first eligible row in order and handle inactive or soft-deleted prior selections. Assert identity, flags and non-trashed counts.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrderStatuses\EnsureInitialWorkOrderStatus;
use App\Enums\BaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;

it('creates the initial status for a tenant', function (): void {
    $team = Team::factory()->createOne();

    $workOrderStatus = resolve(EnsureInitialWorkOrderStatus::class)->handle($team);

    $workOrderStatusCount = $team->workOrderStatuses()->count();

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'team_id' => $team->id,
        'base_status' => BaseStatus::Open,
        'is_initial' => true,
        'is_member_visible' => false,
        'name' => 'Open',
    ]);

    expect($workOrderStatusCount)->toBe(1);
});

it('is idempotent when an initial status already exists', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->initial()->createOne();

    $resolvedWorkOrderStatus = resolve(EnsureInitialWorkOrderStatus::class)
        ->handle($workOrderStatus->team);

    $workOrderStatusCount = $workOrderStatus->team->workOrderStatuses()->count();

    expect($resolvedWorkOrderStatus->is($workOrderStatus))->toBeTrue()
        ->and($workOrderStatusCount)->toBe(1);
});

it('promotes an existing active open status instead of creating a duplicate', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->createOne();

    $laterWorkOrderStatus = WorkOrderStatus::factory()
        ->recycle($workOrderStatus->team)
        ->createOne();

    $resolvedWorkOrderStatus = resolve(EnsureInitialWorkOrderStatus::class)
        ->handle($workOrderStatus->team);

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'is_initial' => true,
    ]);
    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $laterWorkOrderStatus->id,
        'is_initial' => false,
    ]);

    $workOrderStatusCount = $workOrderStatus->team->workOrderStatuses()->count();

    expect($resolvedWorkOrderStatus->is($workOrderStatus))->toBeTrue()
        ->and($workOrderStatusCount)->toBe(2);
});

it('ignores deactivated initial statuses', function (): void {
    $deactivatedInitialWorkOrderStatus = WorkOrderStatus::factory()
        ->initial()
        ->deactivated()
        ->createOne();

    $workOrderStatus = WorkOrderStatus::factory()
        ->recycle($deactivatedInitialWorkOrderStatus->team)
        ->createOne();

    $resolvedWorkOrderStatus = resolve(EnsureInitialWorkOrderStatus::class)
        ->handle($workOrderStatus->team);

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'is_initial' => true,
    ]);
    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $deactivatedInitialWorkOrderStatus->id,
        'is_initial' => false,
    ]);

    $workOrderStatusCount = $workOrderStatus->team->workOrderStatuses()->count();

    expect($resolvedWorkOrderStatus->is($workOrderStatus))->toBeTrue()
        ->and($workOrderStatusCount)->toBe(2);
});

it('ignores soft deleted initial statuses', function (): void {
    $softDeletedInitialWorkOrderStatus = WorkOrderStatus::factory()
        ->initial()
        ->trashed()
        ->createOne();

    $workOrderStatus = WorkOrderStatus::factory()
        ->recycle($softDeletedInitialWorkOrderStatus->team)
        ->createOne();

    $resolvedWorkOrderStatus = resolve(EnsureInitialWorkOrderStatus::class)
        ->handle($workOrderStatus->team);

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'is_initial' => true,
    ]);
    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $softDeletedInitialWorkOrderStatus->id,
        'is_initial' => false,
    ]);

    $workOrderStatusCount = $workOrderStatus->team->workOrderStatuses()->count();

    expect($resolvedWorkOrderStatus->is($workOrderStatus))->toBeTrue()
        ->and($workOrderStatusCount)->toBe(1);
});
```
