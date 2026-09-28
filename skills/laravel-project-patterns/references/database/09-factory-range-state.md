# Factories: Configure Numeric Ranges

Derive each team from its parent before other attributes. Keep country, rounding mode and increment consistent. `forRange()` accepts an explicit nullable maximum; `openEnded()` clears only the upper bound.

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Enums\RoundingMode;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanRule>
 */
class PlanRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_plan_id' => ServicePlan::factory(),
            'team_id' => $this->teamIdFor(...),

            'country_code' => fake()->randomElement(CountryCode::values()),
            'currency_code' => CurrencyCode::USD,
            'minimum_chargeable_weight' => fake()->randomFloat(2, 0, 20),
            'name' => fake()->words(3, true),
            'rounding_increment' => null,
            'rounding_mode' => RoundingMode::None,
        ];
    }

    public function forCountry(CountryCode $countryCode): static
    {
        return $this->state([
            'country_code' => $countryCode,
        ]);
    }

    public function roundUp(): static
    {
        return $this->state([
            'rounding_increment' => 1,
            'rounding_mode' => RoundingMode::Up,
        ]);
    }

    public function withoutRounding(): static
    {
        return $this->state([
            'rounding_increment' => null,
            'rounding_mode' => RoundingMode::None,
        ]);
    }

    /**
     * @param array{service_plan_id: int} $attributes
     */
    private function teamIdFor(array $attributes): int
    {
        $servicePlanId = $attributes['service_plan_id'];

        return ServicePlan::query()
            ->withTrashed()
            ->findOrFail($servicePlanId)
            ->team_id;
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlanRate;
use App\Models\PlanRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanRate>
 */
class PlanRateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'plan_rule_id' => PlanRule::factory(),
            'team_id' => $this->teamIdFor(...),

            'maximum_weight' => fake()->randomFloat(2, 11, 20),
            'minimum_weight' => fake()->randomFloat(2, 0, 10),
            'name' => fake()->word(),
            'rate' => fake()->randomFloat(2, 2, 10),
        ];
    }

    public function forRange(float $minimum, null|float $maximum): static
    {
        return $this->state([
            'maximum_weight' => $maximum,
            'minimum_weight' => $minimum,
        ]);
    }

    public function openEnded(): static
    {
        return $this->state(['maximum_weight' => null]);
    }

    /**
     * @param array{plan_rule_id: int} $attributes
     */
    private function teamIdFor(array $attributes): int
    {
        $planRuleId = $attributes['plan_rule_id'];

        return PlanRule::query()
            ->withTrashed()
            ->findOrFail($planRuleId)
            ->team_id;
    }
}
```
