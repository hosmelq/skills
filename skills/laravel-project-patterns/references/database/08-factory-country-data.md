# Database: Factory Country Data

Generate consistent country, city, province and phone data in factories from the project's country enum and the world lookup database, and offer a state that pins the country.

- Share generators through a factory concern; pick a city of the chosen country from the world connection so city and province stay valid together.
- Build phone numbers from libphonenumber's mobile example for the country and make them unique, so they pass E.164 casting and unique indexes. An optional phone number wraps the generator in `fake()->optional()->passthrough(...)`.
- Resolve one country per definition and reuse it for every country-dependent column; a country state recomputes them all in a closure.

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
```

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CountryCode;
use App\Models\Client;
use App\Models\ClientAddress;
use Database\Factories\Concerns\GeneratesCountryData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientAddress>
 */
final class ClientAddressFactory extends Factory
{
    use GeneratesCountryData;

    public function default(): static
    {
        return $this->state([
            'is_default' => true,
        ]);
    }

    public function definition(): array
    {
        $countryData = $this->generateCountryLocationData();

        return [
            'client_id' => Client::factory(),
            'clinic_id' => $this->clinicIdFor(...),

            'address1' => fake()->streetAddress(),
            'address2' => fake()->secondaryAddress(), // @phpstan-ignore-line - this method exists
            'city' => $countryData['city'],
            'company' => fake()->company(),
            'country_code' => $countryData['country_code'],
            'first_name' => fake()->firstName(),
            'is_default' => false,
            'label' => fake()->randomElement(['Home', 'Office', 'Work']),
            'last_name' => fake()->lastName(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'phone_number' => $this->generateCountryPhoneNumber($countryData['country_code']),
            'postal_code' => fake()->postcode(),
            'province_code' => $countryData['province_code'],
        ];
    }

    public function withCountryCode(CountryCode $countryCode): static
    {
        return $this->state(fn (): array => [
            ...$this->generateCountryLocationData($countryCode),
            'phone_number' => $this->generateCountryPhoneNumber($countryCode),
        ]);
    }

    /**
     * @param array{client_id: int} $attributes
     */
    private function clinicIdFor(array $attributes): int
    {
        $clientId = $attributes['client_id'];

        return Client::query()->withTrashed()->findOrFail($clientId)->clinic_id;
    }
}
```
