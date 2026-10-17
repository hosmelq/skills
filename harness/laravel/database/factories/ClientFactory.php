<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Clinic;
use Database\Factories\Concerns\GeneratesCountryData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
final class ClientFactory extends Factory
{
    use GeneratesCountryData;

    public function definition(): array
    {
        $countryCode = $this->generateCountryCode();

        return [
            'clinic_id' => Clinic::factory(),

            'email' => fake()->unique()->regexify('\w{8}@gmail\.com'),
            'first_name' => fake()->optional()->firstName(),
            'last_name' => fake()->optional()->lastName(),
            'phone_number' => fake()
                ->optional()
                ->passthrough($this->generateCountryPhoneNumber($countryCode)),
        ];
    }

    public function withDefaultAddress(): static
    {
        return $this->afterCreating(function (Client $client): void {
            ClientAddress::factory()->for($client)->createOne(['is_default' => true]);
        });
    }
}
