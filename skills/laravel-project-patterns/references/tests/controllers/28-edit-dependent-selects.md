# Edit Tests: Stored Dependent Options And Partial Reload

GET edit geographic selects in direct and nested forms: null query-selection prop, full country enums, options from the stored country, and a selected-country partial reload. Initial responses check public IDs and option[0] label/value; the direct page also checks its type enum.

The reload examples supply the same country stored on the record and assert the first ordered option. They do not prove switching to a different country, complete-list equality, or exclusion of other countries. The geography fixture is preseeded.

## Direct Form

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\CountryCode;
use App\Enums\FacilityType;
use App\Models\Facility;
use App\Models\World\State;
use Inertia\Testing\AssertableInertia;

describe('edit', function (): void {
    it('shows the edit page', function (): void {
        $facility = Facility::factory()->createOne();

        $state = State::query()
            ->where('country_code', $facility->country_code)
            ->orderBy('name')
            ->first();

        login(team: $facility->team);

        $response = get(route('teams.facilities.edit', [
            'team' => $facility->team,
            'facility' => $facility,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($facility, $state): void {
                $page->component('facilities/Edit')
                    ->whereNull('countryCode')
                    ->where('countryCodes', CountryCode::options())
                    ->where('facility.id', $facility->public_id)
                    ->where('facilityTypes', FacilityType::options())
                    ->where('team.id', $facility->team->public_id)
                    ->where('provinces.0.label', $state->name)
                    ->where('provinces.0.value', $state->iso2);
            });
    });

    it('loads dependent options for the selected value', function (): void {
        $facility = Facility::factory()->createOne();

        $state = State::query()
            ->where('country_code', $facility->country_code)
            ->orderBy('name')
            ->first();

        login(team: $facility->team);

        $response = get(route('teams.facilities.edit', [
            'team' => $facility->team,
            'facility' => $facility,
            'country_code' => $state->country_code,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($state): void {
                $page->component('facilities/Edit')
                    ->reloadOnly('provinces', function (AssertableInertia $reload) use ($state): void {
                        $reload->where('provinces.0.label', $state->name)
                            ->where('provinces.0.value', $state->iso2);
                    });
            });
    });
});
```

## Nested Form

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\CountryCode;
use App\Models\MemberAddress;
use App\Models\World\State;
use Inertia\Testing\AssertableInertia;

describe('edit', function (): void {
    it('shows the edit page', function (): void {
        $address = MemberAddress::factory()->createOne();

        $state = State::query()
            ->where('country_code', $address->country_code)
            ->orderBy('name')
            ->firstOrFail();

        login(team: $address->member->team);

        $response = get(route('teams.members.addresses.edit', [
            'team' => $address->member->team,
            'member' => $address->member,
            'address' => $address,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($address, $state): void {
                $page->component('members/addresses/Edit')
                    ->where('address.id', $address->public_id)
                    ->whereNull('countryCode')
                    ->where('countryCodes', CountryCode::options())
                    ->where('member.id', $address->member->public_id)
                    ->where('team.id', $address->member->team->public_id)
                    ->where('provinces.0.label', $state->name)
                    ->where('provinces.0.value', $state->iso2);
            });
    });

    it('loads dependent options for the selected value', function (): void {
        $address = MemberAddress::factory()->createOne();

        $state = State::query()
            ->where('country_code', $address->country_code)
            ->orderBy('name')
            ->firstOrFail();

        login(team: $address->member->team);

        $response = get(route('teams.members.addresses.edit', [
            'team' => $address->member->team,
            'member' => $address->member,
            'address' => $address,
            'country_code' => $state->country_code,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($state): void {
                $page->component('members/addresses/Edit')
                    ->reloadOnly('provinces', function (AssertableInertia $reload) use ($state): void {
                        $reload->where('provinces.0.label', $state->name)
                            ->where('provinces.0.value', $state->iso2);
                    });
            });
    });
});
```
