# Model Tests: State Resource

Exact resource JSON for an ordered state: base enum value, visibility, initial/final flags and explicit sort order. Separate cases cover final-state classification and deactivation formatting.

Set sort order through the query builder, then refresh, when the creation hook assigns it automatically.

```php
<?php

declare(strict_types=1);

use App\Enums\BaseStatus;
use App\Models\WorkOrderStatus;

it('formats resource correctly', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->createOne([
        'base_status' => BaseStatus::Ready,
        'color' => '#2563eb',
        'description' => 'Available at the counter.',
        'is_member_visible' => true,
        'is_initial' => false,
        'name' => 'Ready',
    ]);
    WorkOrderStatus::query()->whereKey($workOrderStatus)->update(['sort_order' => 7]);
    $workOrderStatus->refresh();

    $resource = json_decode($workOrderStatus->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'base_status' => 'ready',
        'color' => '#2563eb',
        'created_at' => $workOrderStatus->created_at->toJSON(),
        'deactivated_at' => null,
        'description' => 'Available at the counter.',
        'id' => $workOrderStatus->public_id,
        'is_member_visible' => true,
        'is_final' => false,
        'is_initial' => false,
        'name' => 'Ready',
        'sort_order' => 7,
        'updated_at' => $workOrderStatus->updated_at->toJSON(),
    ]);
});

it('formats final states correctly', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()
        ->withBaseStatus(BaseStatus::Completed)
        ->createOne();

    $resource = json_decode($workOrderStatus->toResource()->toJson(), true);

    expect($resource)
        ->base_status->toBe(BaseStatus::Completed->value)
        ->is_final->toBeTrue();
});

it('formats the deactivation timestamp when present', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->deactivated()->createOne();

    $resource = json_decode($workOrderStatus->toResource()->toJson(), true);

    expect($resource)
        ->toHaveKey('deactivated_at')
        ->deactivated_at->toBe($workOrderStatus->deactivated_at->toJSON());
});
```
