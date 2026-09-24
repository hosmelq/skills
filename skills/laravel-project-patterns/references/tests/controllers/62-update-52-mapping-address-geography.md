# Update Tests: Mapping Address Geography

Pest PATCH update: Address update maps its submitted values, reuses the stored country when the submitted country is empty, and clears the province when changing country without one.

Use valid country/province pairs from the consuming suite. Empty country context and changing country without a province are separate cases.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\MemberAddresses\Inputs\UpdateMemberAddressInput;
use App\Actions\MemberAddresses\UpdateMemberAddress;
use App\Enums\CountryCode;
use App\Models\MemberAddress;
use App\Models\World\State;

describe('update', function (): void {
    it('updates the record', function (): void {
        $address = MemberAddress::factory()->createOne();

        $state = State::query()
            ->where('country_code', CountryCode::Nicaragua)
            ->orderBy('name')
            ->firstOrFail();

        login(team: $address->member->team);

        mock(UpdateMemberAddress::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (MemberAddress $addressArgument, UpdateMemberAddressInput $input): bool => $addressArgument->is($address)
                && $input->label === 'Work'
                && $input->provinceCode === $state->iso2);

        $response = patch(route('teams.members.addresses.update', [
            'team' => $address->member->team,
            'member' => $address->member,
            'address' => $address,
        ]), [
            'country_code' => CountryCode::Nicaragua->value,
            'label' => 'Work',
            'province_code' => $state->iso2,
        ]);

        $response->assertRedirectToRoute('teams.members.addresses.index', [
            'team' => $address->member->team,
            'member' => $address->member,
        ])
            ->assertToast('Address updated');
    });

    it('maps the province using the current country when country is empty', function (): void {
        $address = MemberAddress::factory()->createOne([
            'country_code' => CountryCode::Nicaragua,
        ]);

        login(team: $address->member->team);

        mock(UpdateMemberAddress::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (MemberAddress $addressArgument, UpdateMemberAddressInput $input): bool => $addressArgument->is($address)
                && $input->provinceCode === 'MN');

        $response = patch(route('teams.members.addresses.update', [
            'team' => $address->member->team,
            'member' => $address->member,
            'address' => $address,
        ]), [
            'country_code' => '',
            'province_code' => 'MN',
        ]);

        $response->assertRedirectToRoute('teams.members.addresses.index', [
            'team' => $address->member->team,
            'member' => $address->member,
        ])
            ->assertToast('Address updated');
    });

    it('clears the province when changing country without a province', function (): void {
        $address = MemberAddress::factory()->createOne([
            'country_code' => CountryCode::Nicaragua,
            'province_code' => 'MN',
        ]);

        login(team: $address->member->team);

        mock(UpdateMemberAddress::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (MemberAddress $addressArgument, UpdateMemberAddressInput $input): bool => $addressArgument->is($address)
                && $input->countryCode === CountryCode::CostaRica
                && $input->provinceCode === null);

        $response = patch(route('teams.members.addresses.update', [
            'team' => $address->member->team,
            'member' => $address->member,
            'address' => $address,
        ]), [
            'country_code' => CountryCode::CostaRica->value,
        ]);

        $response->assertRedirectToRoute('teams.members.addresses.index', [
            'team' => $address->member->team,
            'member' => $address->member,
        ])
            ->assertToast('Address updated');
    });
});
```
