# Action Tests: Create Address Fields

Integration tests for creating an owner-scoped address: full and required-only input, seeded province lookup, normalized phone, coordinates, ownership and a false default flag.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\MemberAddresses\CreateMemberAddress;
use App\Actions\MemberAddresses\Inputs\CreateMemberAddressInput;
use App\Enums\CountryCode;
use App\Models\Member;
use App\Models\MemberAddress;
use App\Models\World\State;

it('creates a record', function (): void {
    $member = Member::factory()->createOne();

    $state = State::query()
        ->where('country_code', CountryCode::UnitedStates)
        ->orderBy('name')
        ->firstOrFail();

    $address = resolve(CreateMemberAddress::class)->handle(
        $member,
        CreateMemberAddressInput::from([
            'address1' => '123 Main St',
            'address2' => 'Apt 4B',
            'city' => 'Portland',
            'company' => 'Test Company',
            'country_code' => CountryCode::UnitedStates->value,
            'first_name' => 'Alex',
            'label' => 'Home',
            'last_name' => 'Example',
            'latitude' => 45.5152,
            'longitude' => -122.6784,
            'phone_number' => '+1 415 555 0110',
            'postal_code' => '10001',
            'province_code' => $state->iso2,
        ]),
    );

    expect($address)->toBeInstanceOf(MemberAddress::class);

    assertDatabaseHas(MemberAddress::class, [
        'id' => $address->id,
        'member_id' => $member->id,
        'team_id' => $member->team_id,
        'address1' => '123 Main St',
        'address2' => 'Apt 4B',
        'city' => 'Portland',
        'company' => 'Test Company',
        'country_code' => CountryCode::UnitedStates->value,
        'first_name' => 'Alex',
        'is_default' => false,
        'label' => 'Home',
        'last_name' => 'Example',
        'latitude' => 45.5152,
        'longitude' => -122.6784,
        'phone_number' => '+14155550110',
        'postal_code' => '10001',
        'province_code' => $state->iso2,
    ]);
});

it('creates a record with only required fields', function (): void {
    $member = Member::factory()->createOne();

    $address = resolve(CreateMemberAddress::class)->handle(
        $member,
        CreateMemberAddressInput::from([
            'country_code' => CountryCode::UnitedStates->value,
        ]),
    );

    assertDatabaseHas(MemberAddress::class, [
        'id' => $address->id,
        'member_id' => $member->id,
        'team_id' => $member->team_id,
        'address1' => null,
        'address2' => null,
        'city' => null,
        'company' => null,
        'country_code' => CountryCode::UnitedStates->value,
        'first_name' => null,
        'is_default' => false,
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
