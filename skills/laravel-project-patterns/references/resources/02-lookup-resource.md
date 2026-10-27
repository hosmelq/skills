# Resources: Lookup Resource

Add display names from lookup tables and translations to a resource, such as the translated country and the province name for stored codes, caching lookups per process so collections do not query per row.

- Cache lookup rows in static arrays keyed by code; a missing province serializes as `null`.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\CountryCode;
use App\Models\ClientAddress;
use App\Models\World\Country;
use App\Models\World\State;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property ClientAddress $resource
 */
final class ClientAddressResource extends JsonResource
{
    /**
     * @var array<string, Country>
     */
    private static array $countriesByIso2 = [];

    /**
     * @var array<string, array<string, State>>
     */
    private static array $statesByCountryIso2 = [];

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        $country = $this->resolveCountry($this->resource->country_code);
        $province = $this->resource->province_code === null
            ? null
            : $this->resolveProvince($country, $this->resource->province_code);

        return [
            'address1' => $this->resource->address1,
            'address2' => $this->resource->address2,
            'city' => $this->resource->city,
            'company' => $this->resource->company,
            'country' => trans()->string($this->resource->country_code->translationKey()),
            'country_code' => $this->resource->country_code,
            'created_at' => $this->resource->created_at,
            'first_name' => $this->resource->first_name,
            'id' => $this->resource->sqid,
            'is_default' => $this->resource->is_default,
            'label' => $this->resource->label,
            'last_name' => $this->resource->last_name,
            'latitude' => $this->resource->latitude,
            'longitude' => $this->resource->longitude,
            'phone_number' => $this->resource->phone_number?->formatE164(),
            'postal_code' => $this->resource->postal_code,
            'province' => $province?->name,
            'province_code' => $this->resource->province_code,
            'updated_at' => $this->resource->updated_at,
        ];
    }

    private function resolveCountry(CountryCode $countryCode): Country
    {
        if (! array_key_exists($countryCode->value, self::$countriesByIso2)) {
            self::$countriesByIso2[$countryCode->value] = Country::query()
                ->where('iso2', $countryCode)
                ->firstOrFail();
        }

        return self::$countriesByIso2[$countryCode->value];
    }

    private function resolveProvince(Country $country, string $provinceCode): null|State
    {
        assert(is_string($country->iso2));

        if (! array_key_exists($country->iso2, self::$statesByCountryIso2)) {
            /** @var array<string, State> $states */
            $states = $country->states()->get()->keyBy('iso2')->all();

            self::$statesByCountryIso2[$country->iso2] = $states;
        }

        return self::$statesByCountryIso2[$country->iso2][$provinceCode] ?? null;
    }
}
```

```php
<?php

declare(strict_types=1);

use App\Enums\CountryCode;
use App\Models\ClientAddress;

it('formats resource correctly', function (): void {
    $address = ClientAddress::factory()->createOne([
        'address1' => 'Rotonda Jean Paul Genie',
        'address2' => 'Casa 12',
        'city' => 'Managua',
        'company' => 'Vet Labs',
        'country_code' => CountryCode::Nicaragua,
        'first_name' => 'Jane',
        'is_default' => true,
        'label' => 'Office',
        'last_name' => 'Doe',
        'latitude' => 12.114_993,
        'longitude' => -86.236_174,
        'phone_number' => '+505 8888 2222',
        'postal_code' => '14038',
        'province_code' => 'MN',
    ]);
    $resource = json_decode($address->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'address1' => 'Rotonda Jean Paul Genie',
        'address2' => 'Casa 12',
        'city' => 'Managua',
        'company' => 'Vet Labs',
        'country' => 'Nicaragua',
        'country_code' => 'NI',
        'created_at' => $address->created_at->toJSON(),
        'first_name' => 'Jane',
        'id' => $address->sqid,
        'is_default' => true,
        'label' => 'Office',
        'last_name' => 'Doe',
        'latitude' => 12.114_993,
        'longitude' => -86.236_174,
        'phone_number' => '+50588882222',
        'postal_code' => '14038',
        'province' => 'Managua',
        'province_code' => 'MN',
        'updated_at' => $address->updated_at->toJSON(),
    ]);
});
```
