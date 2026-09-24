# Action Tests: Update Address Fields

Integration tests for address updates: all fields, omitted-field preservation and explicit nulls, including seeded province selection, country, coordinates and phone normalization.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\MemberAddresses\Inputs\UpdateMemberAddressInput;
use App\Actions\MemberAddresses\UpdateMemberAddress;
use App\Enums\CountryCode;
use App\Models\MemberAddress;
use App\Models\World\State;

it('updates a record', function (): void {
    $address = MemberAddress::factory()->createOne([
        'address1' => 'Old Address',
        'city' => 'Old City',
    ]);

    $state = State::query()
        ->where('country_code', CountryCode::UnitedStates)
        ->orderBy('name')
        ->firstOrFail();

    $updatedAddress = resolve(UpdateMemberAddress::class)->handle(
        $address,
        UpdateMemberAddressInput::from([
            'address1' => '123 Main St',
            'address2' => 'Apt 4B',
            'city' => 'Portland',
            'company' => 'Test Company',
            'country_code' => CountryCode::UnitedStates->value,
            'first_name' => 'Alex',
            'label' => 'Work',
            'last_name' => 'Example',
            'latitude' => 45.5152,
            'longitude' => -122.6784,
            'phone_number' => '+1 415 555 0110',
            'postal_code' => '10001',
            'province_code' => $state->iso2,
        ]),
    );

    expect($updatedAddress->is($address))->toBeTrue();

    assertDatabaseHas(MemberAddress::class, [
        'id' => $address->id,
        'address1' => '123 Main St',
        'address2' => 'Apt 4B',
        'city' => 'Portland',
        'company' => 'Test Company',
        'country_code' => CountryCode::UnitedStates->value,
        'first_name' => 'Alex',
        'is_default' => false,
        'label' => 'Work',
        'last_name' => 'Example',
        'latitude' => 45.5152,
        'longitude' => -122.6784,
        'phone_number' => '+14155550110',
        'postal_code' => '10001',
        'province_code' => $state->iso2,
    ]);
});

it('updates only provided fields', function (): void {
    $address = MemberAddress::factory()->createOne([
        'address1' => '123 Main St',
        'city' => 'Portland',
        'label' => 'Home',
    ]);

    resolve(UpdateMemberAddress::class)->handle(
        $address,
        UpdateMemberAddressInput::from([
            'label' => 'Work',
        ]),
    );

    assertDatabaseHas(MemberAddress::class, [
        'id' => $address->id,
        'address1' => '123 Main St',
        'city' => 'Portland',
        'label' => 'Work',
    ]);
});

it('clears nullable fields', function (): void {
    $address = MemberAddress::factory()->withCountryCode(CountryCode::UnitedStates)->createOne([
        'address1' => '123 Main St',
        'address2' => 'Apt 4B',
        'city' => 'Portland',
        'company' => 'Test Company',
        'first_name' => 'Alex',
        'label' => 'Home',
        'last_name' => 'Example',
        'latitude' => 45.5152,
        'longitude' => -122.6784,
        'phone_number' => '+14155550110',
        'postal_code' => '10001',
        'province_code' => 'OR',
    ]);

    resolve(UpdateMemberAddress::class)->handle(
        $address,
        UpdateMemberAddressInput::from([
            'address1' => null,
            'address2' => null,
            'city' => null,
            'company' => null,
            'first_name' => null,
            'label' => null,
            'last_name' => null,
            'latitude' => null,
            'longitude' => null,
            'phone_number' => null,
            'postal_code' => null,
            'province_code' => null,
        ]),
    );

    assertDatabaseHas(MemberAddress::class, [
        'id' => $address->id,
        'address1' => null,
        'address2' => null,
        'city' => null,
        'company' => null,
        'first_name' => null,
        'label' => null,
        'last_name' => null,
        'latitude' => null,
        'longitude' => null,
        'phone_number' => null,
        'postal_code' => null,
        'province_code' => null,
    ]);
});
```
