# Model Tests: Resource Deletion Timestamp

Exact resource JSON with numeric duration bounds, unit enums and null lifecycle timestamps; a soft-deleted model formats its deletion timestamp.

```php
<?php

declare(strict_types=1);

use App\Enums\TransitTimeUnit;
use App\Enums\WeightUnit;
use App\Models\ServicePlan;

it('formats resource correctly', function (): void {
    $servicePlan = ServicePlan::factory()->createOne([
        'description' => 'Standard service option',
        'estimated_transit_time_unit' => TransitTimeUnit::Days,
        'maximum_estimated_transit_time' => 4,
        'minimum_estimated_transit_time' => 2,
        'name' => 'Priority Service',
        'weight_unit' => WeightUnit::Pounds,
    ]);

    $resource = json_decode($servicePlan->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'created_at' => $servicePlan->created_at->toJSON(),
        'deactivated_at' => null,
        'deleted_at' => null,
        'description' => 'Standard service option',
        'estimated_transit_time_unit' => 'days',
        'id' => $servicePlan->public_id,
        'maximum_estimated_transit_time' => 4,
        'minimum_estimated_transit_time' => 2,
        'name' => 'Priority Service',
        'updated_at' => $servicePlan->updated_at->toJSON(),
        'weight_unit' => 'pounds',
    ]);
});

it('formats the deletion timestamp when present', function (): void {
    $servicePlan = ServicePlan::factory()->trashed()->createOne();

    $resource = json_decode($servicePlan->toResource()->toJson(), true);

    expect($resource['deleted_at'])->toBe($servicePlan->deleted_at->toJSON());
});
```
