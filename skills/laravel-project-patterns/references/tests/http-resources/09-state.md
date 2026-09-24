# HTTP Resource Tests: State Resource

Exact resource JSON for an ordered state: base enum, independent visibility and initial flags, factory-assigned order and timestamps. A separate case formats deactivation.

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
        'is_initial' => false,
        'is_member_visible' => true,
        'name' => 'Ready',
    ]);

    $resource = json_decode($workOrderStatus->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'base_status' => 'ready',
        'color' => '#2563eb',
        'created_at' => $workOrderStatus->created_at->toJSON(),
        'deactivated_at' => null,
        'description' => 'Available at the counter.',
        'id' => $workOrderStatus->sqid,
        'is_initial' => false,
        'is_member_visible' => true,
        'name' => 'Ready',
        'sort_order' => $workOrderStatus->sort_order,
        'updated_at' => $workOrderStatus->updated_at->toJSON(),
    ]);
});

it('formats the deactivation timestamp when present', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->deactivated()->createOne();

    $resource = json_decode($workOrderStatus->toResource()->toJson(), true);

    expect($resource)
        ->toHaveKey('deactivated_at')
        ->deactivated_at->toBe($workOrderStatus->deactivated_at->toJSON());
});
```
