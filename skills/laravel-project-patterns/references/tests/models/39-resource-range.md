# Model Tests: Range Resource

Exact resource JSON for stored lower and upper bounds, amount and name, public ID and timestamps. Nullable and decimal representations follow the inspected model casts.

```php
<?php

declare(strict_types=1);

use App\Models\PlanRate;

it('formats resource correctly', function (): void {
    $rate = PlanRate::factory()->createOne();

    $resource = json_decode($rate->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'created_at' => $rate->created_at->toJSON(),
        'id' => $rate->public_id,
        'maximum_weight' => $rate->maximum_weight,
        'minimum_weight' => $rate->minimum_weight,
        'name' => $rate->name,
        'rate' => $rate->rate,
        'updated_at' => $rate->updated_at->toJSON(),
    ]);
});
```
