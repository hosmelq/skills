# Controllers: Resolve Dependent Options with Closures

Create uses the selected country or an empty list. Edit falls back to the stored country. The inline form returns a collection; the helper form returns an array. Preserve label/value shape and sorting. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CountryCode;
use App\Enums\FacilityType;
use App\Models\Facility;
use App\Models\Team;
use App\Models\World\State;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FacilityController
{
    public function create(Request $request, Team $team): Response
    {
        $countryCode = $request->enum('country_code', CountryCode::class);

        return Inertia::render('facilities/Create', [
            'countryCode' => $countryCode,
            'countryCodes' => CountryCode::options(),
            'facilityTypes' => FacilityType::options(),
            'provinces' => function () use ($countryCode) {
                if ($countryCode === null) {
                    return [];
                }

                return State::query()
                    ->where('country_code', $countryCode)
                    ->orderBy('name')
                    ->get()
                    ->map(fn (State $state): array => [
                        'label' => $state->name,
                        'value' => $state->iso2,
                    ])
                    ->values();
            },
            'team' => $team->toResource(),
        ]);
    }

    public function edit(Request $request, Team $team, Facility $facility): Response
    {
        $countryCode = $request->enum('country_code', CountryCode::class);

        return Inertia::render('facilities/Edit', [
            'countryCode' => $countryCode,
            'countryCodes' => CountryCode::options(),
            'facility' => $facility->toResource(),
            'facilityTypes' => FacilityType::options(),
            'provinces' => function () use ($countryCode, $facility) {
                return State::query()
                    ->where('country_code', $countryCode ?? $facility->country_code)
                    ->orderBy('name')
                    ->get()
                    ->map(fn (State $state): array => [
                        'label' => $state->name,
                        'value' => $state->iso2,
                    ])
                    ->values();
            },
            'team' => $team->toResource(),
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CountryCode;
use App\Models\Member;
use App\Models\MemberAddress;
use App\Models\Team;
use App\Models\World\State;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberAddressController
{
    public function create(Request $request, Team $team, Member $member): Response
    {
        $countryCode = $request->enum('country_code', CountryCode::class);

        return Inertia::render('members/addresses/Create', [
            'countryCode' => $countryCode,
            'countryCodes' => CountryCode::options(),
            'member' => $member->toResource(),
            'provinces' => fn (): array => $this->provinceOptions($countryCode),
            'team' => $team->toResource(),
        ]);
    }

    public function edit(
        Request $request,
        Team $team,
        Member $member,
        MemberAddress $address
    ): Response {
        $countryCode = $request->enum('country_code', CountryCode::class);

        return Inertia::render('members/addresses/Edit', [
            'address' => $address->toResource(),
            'countryCode' => $countryCode,
            'countryCodes' => CountryCode::options(),
            'member' => $member->toResource(),
            'provinces' => fn (): array => $this->provinceOptions(
                $countryCode ?? $address->country_code,
            ),
            'team' => $team->toResource(),
        ]);
    }

    /**
     * @return array<int, array{label: string, value: null|string}>
     */
    private function provinceOptions(null|CountryCode $countryCode): array
    {
        if (! $countryCode instanceof CountryCode) {
            return [];
        }

        return State::query()
            ->where('country_code', $countryCode)
            ->orderBy('name')
            ->get()
            ->map(fn (State $state): array => [
                'label' => $state->name,
                'value' => $state->iso2,
            ])
            ->values()
            ->all();
    }
}
```
