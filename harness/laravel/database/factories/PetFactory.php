<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WeightUnit;
use App\Models\Client;
use App\Models\Clinic;
use App\Models\Pet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pet>
 */
final class PetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'client_id' => $this->clientFactoryFor(...),

            'body_condition_score' => null,
            'deceased_at' => null,
            'deceased_reason' => null,
            'name' => fake()->firstName(),
            'registered_at' => null,
            'weight' => null,
            'weight_unit' => null,
        ];
    }

    public function withWeight(WeightUnit $weightUnit = WeightUnit::Kilograms): static
    {
        return $this->state([
            'weight' => 1,
            'weight_unit' => $weightUnit,
        ]);
    }

    /**
     * @param array{clinic_id: int} $attributes
     */
    private function clientFactoryFor(array $attributes): ClientFactory
    {
        return Client::factory()->state(['clinic_id' => $attributes['clinic_id']]);
    }
}
