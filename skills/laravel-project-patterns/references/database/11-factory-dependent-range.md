# Factories: Generate Dependent Bounds

Generate the minimum once and use it as the lower bound for the optional maximum. Keep a missing maximum distinct from a numeric value.

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TransitTimeUnit;
use App\Enums\WeightUnit;
use App\Models\ServicePlan;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServicePlan>
 */
class ServicePlanFactory extends Factory
{
    public function deactivated(): static
    {
        return $this->state(['deactivated_at' => now()]);
    }

    public function definition(): array
    {
        $minimumTransitTime = fake()->numberBetween(1, 10);

        return [
            'team_id' => Team::factory(),

            'deactivated_at' => null,
            'description' => fake()->paragraph(),
            'estimated_transit_time_unit' => TransitTimeUnit::Days,
            'maximum_estimated_transit_time' => fake()->optional()
                ->numberBetween($minimumTransitTime, 20),
            'minimum_estimated_transit_time' => $minimumTransitTime,
            'name' => fake()->unique()->bothify('Service Plan ######'),
            'weight_unit' => WeightUnit::Pounds,
        ];
    }
}
```
