# HTTP Resource Tests: Ordered Item Resource

Exact resource JSON for an ordered item with color, description and nullable deactivation; a separate case checks a present deactivation timestamp.

```php
<?php

declare(strict_types=1);

use App\Models\ItemGroup;

it('formats resource correctly', function (): void {
    $itemGroup = ItemGroup::factory()->createOne([
        'color' => '#2563eb',
        'description' => 'Consumer electronics and accessories.',
        'name' => 'Electronics',
    ]);

    $resource = json_decode($itemGroup->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'color' => '#2563eb',
        'created_at' => $itemGroup->created_at->toJSON(),
        'deactivated_at' => null,
        'description' => 'Consumer electronics and accessories.',
        'id' => $itemGroup->sqid,
        'name' => 'Electronics',
        'sort_order' => $itemGroup->sort_order,
        'updated_at' => $itemGroup->updated_at->toJSON(),
    ]);
});

it('formats the deactivation timestamp when present', function (): void {
    $itemGroup = ItemGroup::factory()->deactivated()->createOne();

    $resource = json_decode($itemGroup->toResource()->toJson(), true);

    expect($resource)
        ->toHaveKey('deactivated_at')
        ->deactivated_at->toBe($itemGroup->deactivated_at->toJSON());
});
```
