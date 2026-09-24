# Create Tests: Dependent Select Options And Partial Reload

Complete HTTP/Inertia create-page contract for dependent selects: component, public IDs, enum options, null selected value and empty child list, followed by selected-value echo and ordered partial-reload options. Includes standalone and nested-parent forms.

Use `shows the create page`, then `loads dependent options for the selected value`. Qualify the field pair only when multiple dependent selects need distinct names. These cases do not test browser change handlers, selection clearing or submission validation.

## Facility Form

Use the project's seeded catalog or deterministic fixtures with multiple regions for the selected country; do not invent a region factory. Match `reloadOnly()` to the installed Inertia testing API.

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

        login(team: $team);

        $response = get(route('teams.facilities.create', [
            'team' => $team,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($team): void {
                $page->component('facilities/Create')
                    ->whereNull('countryCode')
                    ->where('countryCodes', CountryCode::options())
                    ->where('facilityTypes', FacilityType::options())
                    ->where('team.id', $team->sqid)
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

        login(team: $team);

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

1. Create a `Member` fixture and use its team for `login(team: $member->team)`.
2. Request `teams.members.addresses.create` with both `team` and `member` route
   parameters; keep `country_code` for the selected-country case.
3. Expect `members/addresses/Create`. On initial load assert `member.id` and
   `team.id` against their public IDs; omit the facility-only `facilityTypes` prop.
4. Keep null country / empty provinces initially and the complete partial-reload
   options assertion unchanged. The parent must be valid in both cases.
