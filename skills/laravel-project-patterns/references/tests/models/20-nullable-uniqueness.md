# Model Tests: Nullable Reference Uniqueness

Database uniqueness for case-insensitive non-null references within a tenant: different-tenant success, multiple null values and reuse after soft deletion.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Models\Team;
use App\Models\WorkOrder;
use Illuminate\Database\UniqueConstraintViolationException;

it('enforces case-insensitive reference uniqueness per tenant at the database level', function (): void {
    $workOrder = WorkOrder::factory()->createOne([
        'reference' => 'Record-100',
    ]);
    $otherTeam = Team::factory()->createOne();

    WorkOrder::factory()->recycle($otherTeam)->createOne([
        'reference' => 'RECORD-100',
    ]);

    assertDatabaseHas(WorkOrder::class, [
        'team_id' => $otherTeam->id,
        'reference' => 'RECORD-100',
    ]);

    expect(fn () => WorkOrder::factory()->recycle($workOrder->team)->createOne([
        'reference' => 'record-100',
    ]))->toThrow(function (UniqueConstraintViolationException $exception): void {
        expect($exception->index)->toBe('work_orders_active_reference_unique');
    });
});

it('allows multiple null references in the same tenant', function (): void {
    $team = Team::factory()->createOne();

    WorkOrder::factory()->count(2)->recycle($team)->create([
        'reference' => null,
    ]);

    $workOrderCount = WorkOrder::query()
        ->whereBelongsTo($team)
        ->whereNull('reference')
        ->count();

    expect($workOrderCount)->toBe(2);
});

it('allows reusing references after soft deletion', function (): void {
    $workOrder = WorkOrder::factory()->trashed()->createOne([
        'reference' => 'Record-100',
    ]);

    $replacement = WorkOrder::factory()->recycle($workOrder->team)->createOne([
        'reference' => 'RECORD-100',
    ]);

    assertDatabaseHas(WorkOrder::class, [
        'id' => $replacement->id,
        'reference' => 'RECORD-100',
    ]);
});
```
