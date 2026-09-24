# HTTP Resource Tests: Nested Address Resource

Exact resource JSON with a nested address, normalized phone, region labels, coordinates, opening-hours arrays, type, nullable deactivation and timestamps.

The fictional geography provider resolves US/CA to United States/California. Empty opening-hour arrays remain arrays.

```php
<?php

declare(strict_types=1);

use App\Models\Facility;

it('formats resource correctly', function (): void {
    $facility = Facility::factory()->createOne([
        'address1' => '200 Example Avenue',
        'address2' => 'Building 2',
        'city' => 'Los Angeles',
        'country_code' => 'US',
        'latitude' => 34.052235,
        'longitude' => -118.243683,
        'name' => 'Demo Service Center',
        'opening_hours' => [
            'monday' => ['08:00-17:00'],
            'saturday' => [],
        ],
        'phone_number' => '+1 415 555 0111',
        'postal_code' => '90001',
        'province_code' => 'CA',
        'type' => 'studio',
    ]);

    $resource = json_decode($facility->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'address' => [
            'address1' => '200 Example Avenue',
            'address2' => 'Building 2',
            'city' => 'Los Angeles',
            'country' => 'United States',
            'country_code' => 'US',
            'latitude' => 34.052235,
            'longitude' => -118.243683,
            'phone_number' => '+14155550111',
            'postal_code' => '90001',
            'province' => 'California',
            'province_code' => 'CA',
        ],
        'created_at' => $facility->created_at->toJSON(),
        'deactivated_at' => $facility->deactivated_at,
        'id' => $facility->sqid,
        'name' => 'Demo Service Center',
        'opening_hours' => [
            'monday' => ['08:00-17:00'],
            'saturday' => [],
        ],
        'type' => 'studio',
        'updated_at' => $facility->updated_at->toJSON(),
    ]);
});
```
