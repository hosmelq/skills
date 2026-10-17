<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    private static null|string $password = null;

    public function definition(): array
    {
        return [
            'current_clinic_id' => null,

            'email' => fake()->unique()->regexify('\w{8}@gmail\.com'),
            'email_verified_at' => now(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'password' => self::$password ??= bcrypt('password'),
            'remember_token' => str()->random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (): array => [
            'email_verified_at' => null,
        ]);
    }

    public function withClinic(null|Clinic $clinic = null): static
    {
        $clinic ??= Clinic::factory()->createOne();

        return $this->state(['current_clinic_id' => $clinic->id])
            ->afterCreating(function (User $user) use ($clinic): void {
                $user->clinics()->syncWithPivotValues($clinic, []);
            });
    }
}
