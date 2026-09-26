# Controllers: Resolve a Dependent Property

Use an Inertia property object for reusable province options. Resolve the selected country on create and the stored fallback on edit; the provider returns a complete ordered array. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CountryCode;
use App\Http\Inertia\World\ProvinceOptions;
use App\Models\Member;
use App\Models\MemberAddress;
use App\Models\Team;
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
            'provinces' => new ProvinceOptions($countryCode),
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
            'provinces' => new ProvinceOptions($countryCode ?? $address->country_code),
            'team' => $team->toResource(),
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Inertia\World;

use App\Enums\CountryCode;
use App\Models\World\State;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Override;

class ProvinceOptions implements ProvidesInertiaProperty
{
    public function __construct(private readonly null|CountryCode $countryCode)
    {
    }

    /**
     * @return list<array{label: string, value: null|string}>
     */
    #[Override]
    public function toInertiaProperty(PropertyContext $context): array
    {
        if (! $this->countryCode instanceof CountryCode) {
            return [];
        }

        return State::query()
            ->where('country_code', $this->countryCode)
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
