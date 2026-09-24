# Model Tests: Code Resource and Loaded Relations

Exact resource JSON with a formatted deactivation timestamp and code/label fields; separately assert the complete loaded member and plan resource payloads.

```php
<?php

declare(strict_types=1);

use App\Models\Cabinet;

it('formats resource correctly', function (): void {
    $cabinet = Cabinet::factory()
        ->deactivated()
        ->createOne([
            'code' => 'DEMO-001',
            'label' => 'Front desk',
        ]);

    $resource = json_decode($cabinet->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'code' => 'DEMO-001',
        'created_at' => $cabinet->created_at->toJSON(),
        'deactivated_at' => $cabinet->deactivated_at->toJSON(),
        'id' => $cabinet->public_id,
        'label' => 'Front desk',
        'updated_at' => $cabinet->updated_at->toJSON(),
    ]);
});

it('includes loaded relationship resources', function (): void {
    $cabinet = Cabinet::factory()->createOne();

    $memberResource = json_decode($cabinet->member->toResource()->toJson(), true);
    $servicePlanResource = json_decode(
        $cabinet->servicePlan->toResource()->toJson(),
        true,
    );

    $resource = json_decode($cabinet->toResource()->toJson(), true);

    expect($resource)
        ->member->toEqual($memberResource)
        ->service_plan->toEqual($servicePlanResource);
});
```
