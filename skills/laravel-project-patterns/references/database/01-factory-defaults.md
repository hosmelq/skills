# Factories: Define Defaults

Return explicit null, false and enum defaults from `definition()`. Generate the related owner through its factory. The code-length constant matches the model default; independent fields may be sorted while relationship dependencies keep their evaluation order.

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AssignmentMode;
use App\Enums\CodeAlphabet;
use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Enums\UnitSystem;
use App\Enums\WeightUnit;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),

            'assignment_mode' => AssignmentMode::RequiresApproval,
            'cabinets_enabled' => false,
            'code_format_alphabet_type' => CodeAlphabet::Alphanumeric,
            'code_format_length' => Team::DEFAULT_CODE_LENGTH,
            'code_format_prefix' => null,
            'contact_email' => fake()->unique()->regexify('\w{8}@gmail\.com'),
            'contact_phone_number' => fake()->unique()->numerify('415 555 ####'),
            'country_code' => CountryCode::UnitedStates,
            'currency_code' => CurrencyCode::USD,
            'name' => fake()->company(),
            'slug' => null,
            'timezone' => 'America/New_York',
            'unit_system' => UnitSystem::Imperial,
            'weight_unit' => WeightUnit::Pounds,
            'work_orders_enabled' => false,
        ];
    }
}
```
