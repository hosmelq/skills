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
    protected function generateCountryCode(): CountryCode
    {
        $countryCode = fake()->randomElement(CountryCode::cases());

        assert($countryCode instanceof CountryCode);

        return $countryCode;
    }

    /**
     * @return array{city: string, country_code: CountryCode, province_code: null|string}
     */
    protected function generateCountryLocationData(null|CountryCode $countryCode = null): array
    {
        $countryCode ??= $this->generateCountryCode();
        $city = City::query()->where('country_code', $countryCode)->inRandomOrder()->firstOrFail();

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
            PhoneNumberType::MOBILE,
        );

        assert($examplePhoneNumber instanceof PhoneNumber);

        return fake()
            ->unique()
            ->numerify(sprintf(
                '+%s %s####',
                $examplePhoneNumber->getCountryCode(),
                mb_substr((string) $examplePhoneNumber->getNationalNumber(), 0, -4),
            ));
    }
}
