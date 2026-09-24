# Model Tests: Flat Address Resource

Exact flat resource JSON for an address: region labels and codes, normalized phone, float coordinates, names, company, default flag, public ID and timestamps.

The fictional geography provider resolves US/CA to United States/California. Adapt codes and expected labels together.

```php
<?php

declare(strict_types=1);

use App\Enums\CountryCode;
use App\Models\MemberAddress;

it('formats resource correctly', function (): void {
    $address = MemberAddress::factory()->createOne([
        'address1' => '100 Example Avenue',
        'address2' => 'Suite 12',
        'city' => 'Los Angeles',
        'company' => 'Example Studio',
        'country_code' => CountryCode::UnitedStates,
        'first_name' => 'Jane',
        'is_default' => true,
        'label' => 'Office',
        'last_name' => 'Doe',
        'latitude' => 34.052235,
        'longitude' => -118.243683,
        'phone_number' => '+1 415 555 0112',
        'postal_code' => '90001',
        'province_code' => 'CA',
    ]);

    $resource = json_decode($address->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'address1' => '100 Example Avenue',
        'address2' => 'Suite 12',
        'city' => 'Los Angeles',
        'company' => 'Example Studio',
        'country' => 'United States',
        'country_code' => 'US',
        'created_at' => $address->created_at->toJSON(),
        'first_name' => 'Jane',
        'id' => $address->public_id,
        'is_default' => true,
        'label' => 'Office',
        'last_name' => 'Doe',
        'latitude' => 34.052235,
        'longitude' => -118.243683,
        'phone_number' => '+14155550112',
        'postal_code' => '90001',
        'province' => 'California',
        'province_code' => 'CA',
        'updated_at' => $address->updated_at->toJSON(),
    ]);
});
```
