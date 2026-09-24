# Action Tests: Create an Addressed Record

Integration tests for creating a tenant-owned record with an address and type enum: complete input and required-only input, geographic fields, normalized phone and null defaults.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\Facilities\CreateFacility;
use App\Actions\Facilities\Inputs\CreateFacilityInput;
use App\Enums\CountryCode;
use App\Enums\FacilityType;
use App\Models\Facility;
use App\Models\Team;

it('creates a record', function (): void {
    $team = Team::factory()->createOne();

    $facility = resolve(CreateFacility::class)->handle(
        $team,
        CreateFacilityInput::from([
            'address1' => '123 Main Street',
            'address2' => 'Suite 100',
            'city' => 'Portland',
            'country_code' => CountryCode::UnitedStates(),
            'latitude' => 45.5152,
            'longitude' => -122.6784,
            'name' => 'Main office',
            'phone_number' => '+1 415 555 0110',
            'postal_code' => '12345',
            'province_code' => 'OR',
            'type' => FacilityType::Office(),
        ]),
    );

    expect($facility)->toBeInstanceOf(Facility::class);

    assertDatabaseHas(Facility::class, [
        'id' => $facility->id,
        'team_id' => $team->id,
        'address1' => '123 Main Street',
        'address2' => 'Suite 100',
        'city' => 'Portland',
        'country_code' => CountryCode::UnitedStates,
        'latitude' => 45.5152,
        'longitude' => -122.6784,
        'name' => 'Main office',
        'phone_number' => '+14155550110',
        'postal_code' => '12345',
        'province_code' => 'OR',
        'type' => FacilityType::Office,
    ]);
});

it('creates a record with only required fields', function (): void {
    $team = Team::factory()->createOne();

    $facility = resolve(CreateFacility::class)->handle(
        $team,
        CreateFacilityInput::from([
            'country_code' => CountryCode::UnitedStates(),
            'name' => 'Main office',
            'type' => FacilityType::Office(),
        ]),
    );

    assertDatabaseHas(Facility::class, [
        'id' => $facility->id,
        'team_id' => $team->id,
        'address1' => null,
        'address2' => null,
        'city' => null,
        'country_code' => CountryCode::UnitedStates,
        'latitude' => null,
        'longitude' => null,
        'name' => 'Main office',
        'phone_number' => null,
        'postal_code' => null,
        'province_code' => null,
        'type' => FacilityType::Office,
    ]);
});
```
