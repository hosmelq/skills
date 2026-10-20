<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Actions\Clinics\EnsureClinicRoles;
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
