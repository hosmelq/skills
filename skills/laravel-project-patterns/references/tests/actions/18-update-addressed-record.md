# Action Tests: Update an Addressed Record

Integration tests for updating a typed record with an address: full input, provided-only fields and explicit-null clearing of geographic and contact fields.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\Facilities\Inputs\UpdateFacilityInput;
use App\Actions\Facilities\UpdateFacility;
use App\Enums\CountryCode;
use App\Enums\FacilityType;
use App\Models\Facility;

it('updates a record', function (): void {
    $facility = Facility::factory()->createOne([
        'name' => 'Main office',
    ]);

    $updatedFacility = resolve(UpdateFacility::class)->handle(
        $facility,
        UpdateFacilityInput::from([
            'address1' => '123 Main St',
            'address2' => 'Apt 4B',
            'city' => 'Vancouver',
            'country_code' => CountryCode::Canada(),
            'latitude' => 45.5152,
            'longitude' => -122.6784,
            'name' => 'Updated studio',
            'phone_number' => '+1 415 555 0111',
            'postal_code' => '12345',
            'province_code' => 'BC',
            'type' => FacilityType::Studio(),
        ]),
    );

    expect($updatedFacility->is($facility))->toBeTrue();

    assertDatabaseHas(Facility::class, [
        'id' => $facility->id,
        'address1' => '123 Main St',
        'address2' => 'Apt 4B',
        'city' => 'Vancouver',
        'country_code' => CountryCode::Canada,
        'latitude' => 45.5152,
        'longitude' => -122.6784,
        'name' => 'Updated studio',
        'phone_number' => '+14155550111',
        'postal_code' => '12345',
        'province_code' => 'BC',
        'type' => FacilityType::Studio,
    ]);
});

it('updates only provided fields', function (): void {
    $facility = Facility::factory()->createOne([
        'address1' => '123 Main Street',
        'name' => 'Main office',
    ]);

    resolve(UpdateFacility::class)->handle(
        $facility,
        UpdateFacilityInput::from(['name' => 'Updated studio']),
    );

    assertDatabaseHas(Facility::class, [
        'id' => $facility->id,
        'address1' => '123 Main Street',
        'name' => 'Updated studio',
    ]);
});

it('clears nullable fields', function (): void {
    $facility = Facility::factory()->createOne([
        'address1' => '123 Main Street',
        'address2' => 'Suite 100',
        'city' => 'Portland',
        'latitude' => 45.5152,
        'longitude' => -122.6784,
        'phone_number' => '+14155550110',
        'postal_code' => '12345',
        'province_code' => 'OR',
    ]);

    resolve(UpdateFacility::class)->handle(
        $facility,
        UpdateFacilityInput::from([
            'address1' => null,
            'address2' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
            'phone_number' => null,
            'postal_code' => null,
            'province_code' => null,
        ]),
    );

    assertDatabaseHas(Facility::class, [
        'id' => $facility->id,
        'address1' => null,
        'address2' => null,
        'city' => null,
        'latitude' => null,
        'longitude' => null,
        'phone_number' => null,
        'postal_code' => null,
        'province_code' => null,
    ]);
});
```
