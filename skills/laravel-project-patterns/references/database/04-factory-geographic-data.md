# Factories: Generate Geographic Data

Use a seeded geography connection to choose a city for the requested country, then read its province code. Generate a mobile example with `giggsey/libphonenumber-for-php-lite`. Missing cities fail; the type assertions depend on PHP assertion settings. The helper queries data even when a caller only builds attributes.

```php
<?php

declare(strict_types=1);

namespace Database\Factories\Concerns;

use App\Enums\CountryCode;
use App\Models\World\City;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

trait GeneratesCountryData
{
    /**
     * @return array{city: string, country_code: CountryCode, province_code: null|string}
     */
    protected function generateCountryAddressData(null|CountryCode $countryCode = null): array
    {
        $countryCode ??= fake()->randomElement(CountryCode::cases());

        assert($countryCode instanceof CountryCode);

        $city = City::query()
            ->where('country_code', $countryCode)
            ->inRandomOrder()
            ->firstOrFail();

        return [
            'city' => $city->name,
            'country_code' => $countryCode,
            'province_code' => $city->state->iso2,
        ];
    }

    protected function generateCountryPhoneNumber(CountryCode $countryCode): string
    {
        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        $examplePhoneNumber = $phoneNumberUtil->getExampleNumberForType(
            $countryCode->value,
            PhoneNumberType::MOBILE
        );

        assert($examplePhoneNumber instanceof PhoneNumber);

        return sprintf(
            '+%s %s',
            $examplePhoneNumber->getCountryCode(),
            $examplePhoneNumber->getNationalNumber(),
        );
    }
}
```
