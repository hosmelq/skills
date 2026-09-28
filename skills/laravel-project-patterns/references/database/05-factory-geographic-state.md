# Factories: Keep Geographic Fields Consistent

Generate the country, city, province and phone together. `withCountryCode()` replaces that dependent group. The nested address derives its team from the generated member, including deleted members; keep `member_id` before the deferred team lookup.

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CountryCode;
use App\Enums\FacilityType;
use App\Models\Facility;
use App\Models\Team;
use Database\Factories\Concerns\GeneratesCountryData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Facility>
 */
class FacilityFactory extends Factory
{
    use GeneratesCountryData;

    public function deactivated(): static
    {
        return $this->state(['deactivated_at' => now()]);
    }

    public function definition(): array
    {
        $countryData = $this->generateCountryAddressData();

        return [
            'team_id' => Team::factory(),

            'address1' => fake()->streetAddress(),
            'address2' => fake()->secondaryAddress(), // @phpstan-ignore-line - this method exists
            'city' => $countryData['city'],
            'country_code' => $countryData['country_code'],
            'deactivated_at' => null,
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'name' => fake()->company(),
            'opening_hours' => null,
            'phone_number' => $this->generateCountryPhoneNumber($countryData['country_code']),
            'postal_code' => fake()->postcode(),
            'province_code' => $countryData['province_code'],
            'type' => FacilityType::Warehouse,
        ];
    }

    public function withCountryCode(CountryCode $countryCode): static
    {
        return $this->state(fn (): array => [
            ...$this->generateCountryAddressData($countryCode),
            'phone_number' => $this->generateCountryPhoneNumber($countryCode),
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CountryCode;
use App\Models\Member;
use App\Models\MemberAddress;
use Database\Factories\Concerns\GeneratesCountryData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberAddress>
 */
class MemberAddressFactory extends Factory
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
        $countryData = $this->generateCountryAddressData();

        return [
            'member_id' => Member::factory(),
            'team_id' => $this->teamIdFor(...),

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
            ...$this->generateCountryAddressData($countryCode),
            'phone_number' => $this->generateCountryPhoneNumber($countryCode),
        ]);
    }

    /**
     * @param array{member_id: int} $attributes
     */
    private function teamIdFor(array $attributes): int
    {
        $memberId = $attributes['member_id'];

        return Member::query()
            ->withTrashed()
            ->findOrFail($memberId)
            ->team_id;
    }
}
```

Use the [geographic helper](04-factory-geographic-data.md).
