# Database: Factory Callbacks and Relation States

Run work that needs the saved record from a factory: set up records every instance requires in `configure()`, and offer opt-in states that attach memberships, create children or associate optional parents after creation.

- Use `configure()` with `afterCreating()` only for setup every created record needs, delegating to the action the application uses for the same setup.
- Opt-in states cover related records a record does not need by default. They run in `afterCreating()`, and everything they create belongs to the record's tenant; an optional `belongsTo` parent is created or reused there, then attached with `associate()` and `save()`.

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Actions\EnsureClinicRoles;
use App\Enums\CurrencyCode;
use App\Models\Clinic;
use App\Models\User;
use Database\Factories\Concerns\GeneratesCountryData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Clinic>
 */
final class ClinicFactory extends Factory
{
    use GeneratesCountryData;

    #[Override]
    public function configure(): static
    {
        return $this->afterCreating(function (Clinic $clinic): void {
            resolve(EnsureClinicRoles::class)->handle($clinic);
        });
    }

    public function definition(): array
    {
        $countryCode = $this->generateCountryCode();

        return [
            'owner_id' => User::factory(),

            'accepts_emergencies' => false,
            'consultation_fee' => '25.00',
            'country_code' => $countryCode,
            'currency_code' => CurrencyCode::USD,
            'latitude' => null,
            'name' => fake()->company(),
            'opening_hours' => null,
            'phone_number' => $this->generateCountryPhoneNumber($countryCode),
            'slug' => null,
            'verified_at' => null,
            'visit_duration_minutes' => 30,
        ];
    }
}
```

```php
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
```

```php
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
```
