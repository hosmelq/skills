# Create Tests: Dependent Select Options And Partial Reload

Use when changing one select changes the options in another: **country → province**,
`country_code` query input → `countryCode` and `provinces` Inertia props.
Also: cascading dropdowns, select dependiente, país → provincia.

## Case Order

1. Initial page: `countryCode = null`, complete country options, empty `provinces`.
2. Selected country: verify the selected value, then partially reload `provinces`;
   compare the complete expected options array to verify membership, count, every
   label/value pair and ordering together.

Put these page cases after authentication, authorization, binding and access
restrictions. Use `loads dependent options for the selected value` consistently;
add the field pair only when multiple dependent selects in the same controller
need distinct test names. This tests the HTTP/Inertia response,
not the browser change handler, clearing a selection or submission validation.

## Facility Form

This fictional example assumes the test bootstrap seeds regions for the selected
country, with multiple regions to make ordering meaningful. Use the project's
seeded catalog or deterministic fixtures; do not invent a region factory.
Models, enum options, `signIn(team: ...)`, `public_id`, named routes and
`reloadOnly()` must match the application's setup and installed Inertia testing API.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\CountryCode;
use App\Enums\FacilityType;
use App\Models\Region;
use App\Models\Team;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('shows the create page', function (): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        $response = get(route('teams.facilities.create', [
            'team' => $team,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($team): void {
                $page->component('facilities/Create')
                    ->whereNull('countryCode')
                    ->where('countryCodes', CountryCode::options())
                    ->where('facilityTypes', FacilityType::options())
                    ->where('team.id', $team->public_id)
                    ->has('provinces', 0);
            });
    });

    it('loads dependent options for the selected value', function (): void {
        $team = Team::factory()->createOne();
        $regions = Region::query()
            ->where('country_code', CountryCode::Canada)
            ->orderBy('name')
            ->get();

        expect($regions->count())->toBeGreaterThan(1);

        $countryCode = $regions->first()->country_code;
        $options = $regions->map(fn (Region $region): array => [
            'label' => $region->name,
            'value' => $region->iso2,
        ])->all();

        signIn(team: $team);

        $response = get(route('teams.facilities.create', [
            'team' => $team,
            'country_code' => $countryCode,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($countryCode, $options): void {
                $page->component('facilities/Create')
                    ->where('countryCode', $countryCode)
                    ->reloadOnly('provinces', function (AssertableInertia $reload) use ($options): void {
                        $reload->where('provinces', $options);
                    });
            });
    });
});
```

## Nested Address Variant

Reuse the same two test names and assertions; only adapt the parent contract:

1. Create a `Member` fixture and use its team for `signIn(team: $member->team)`.
2. Request `teams.members.addresses.create` with both `team` and `member` route
   parameters; keep `country_code` for the selected-country case.
3. Expect `members/addresses/Create`. On initial load assert `member.id` and
   `team.id` against their public IDs; omit the facility-only `facilityTypes` prop.
4. Keep null country / empty provinces initially and the complete partial-reload
   options assertion unchanged. The parent must be valid in both cases.

## Related References

1. [Ordered create block](00-create-test-order.md)
2. [Parent scope and binding](01-create-route-bindings.md)
