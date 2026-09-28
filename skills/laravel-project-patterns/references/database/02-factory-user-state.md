# Factories: Configure User State

Cache the default password hash, provide an unverified state, and attach membership after creating the user. Calling `withTeam()` without an existing team creates that team immediately, before the user is made or created.

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static null|string $password = null;

    public function definition(): array
    {
        return [
            'current_team_id' => null,

            'email' => fake()->unique()->regexify('\w{8}@gmail\.com'),
            'email_verified_at' => now(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (): array => [
            'email_verified_at' => null,
        ]);
    }

    public function withTeam(null|Team $team = null): static
    {
        $team ??= Team::factory()->createOne();

        return $this->state(['current_team_id' => $team->id])
            ->afterCreating(function (User $user) use ($team): void {
                $user->teams()->syncWithPivotValues($team, []);
            });
    }
}
```
