# HTTP Resources: Nested Address with Cached Labels

Nest address fields and retain opening-hour arrays, including empty days. Country and province lookup rules match the flat shape; the phone field passes its cast value through directly.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\CountryCode;
use App\Models\Facility;
use App\Models\World\Country;
use App\Models\World\State;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property Facility $resource
 */
class FacilityResource extends JsonResource
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
            'address' => [
                'address1' => $this->resource->address1,
                'address2' => $this->resource->address2,
                'city' => $this->resource->city,
                'country' => $country->name,
                'country_code' => $this->resource->country_code,
                'latitude' => $this->resource->latitude,
                'longitude' => $this->resource->longitude,
                'phone_number' => $this->resource->phone_number,
                'postal_code' => $this->resource->postal_code,
                'province' => $province?->name,
                'province_code' => $this->resource->province_code,
            ],
            'created_at' => $this->resource->created_at,
            'deactivated_at' => $this->resource->deactivated_at,
            'id' => $this->resource->sqid,
            'name' => $this->resource->name,
            'opening_hours' => $this->resource->opening_hours,
            'type' => $this->resource->type,
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
