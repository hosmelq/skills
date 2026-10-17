<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OneTimePassword;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OneTimePassword>
 */
final class OneTimePasswordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->numerify('######'),
            'email' => fake()->safeEmail(),
            'expires_at' => now()->addMinutes(10),
            'used_at' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subMinute()]);
    }

    public function used(): static
    {
        return $this->state(['used_at' => now()]);
    }
}
