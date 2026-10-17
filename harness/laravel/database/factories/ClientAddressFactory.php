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
