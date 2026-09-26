# HTTP Resources: Flat Address with Cached Labels

Return address fields directly. Cache country and province lookups per resource class; a missing country fails, while a null or unknown province remains null. The flat phone field uses E.164.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\CountryCode;
use App\Models\MemberAddress;
use App\Models\World\Country;
use App\Models\World\State;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property MemberAddress $resource
 */
class MemberAddressResource extends JsonResource
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
            'country' => $country->name,
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
            $states = $country->states()
                ->get()
                ->keyBy('iso2')
                ->all();

            self::$statesByCountryIso2[$country->iso2] = $states;
        }

        return self::$statesByCountryIso2[$country->iso2][$provinceCode] ?? null;
    }
}
```
