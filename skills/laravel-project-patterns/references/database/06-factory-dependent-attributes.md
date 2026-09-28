# Factories: Derive Related Attributes

Evaluate the member first, derive its team, then generate a plan for that team. Generate `code` before `normalized_code`; those dependencies must stay ordered. Deactivation sets its timestamp independently from soft deletion.

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Cabinet;
use App\Models\Member;
use App\Models\ServicePlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cabinet>
 */
class CabinetFactory extends Factory
{
    public function deactivated(): static
    {
        return $this->state(['deactivated_at' => now()]);
    }

    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'team_id' => $this->teamIdFor(...),
            'service_plan_id' => $this->servicePlanFactoryFor(...),

            'code' => $code = fake()->unique()->numerify('CB-######'),
            'deactivated_at' => null,
            'label' => fake()->optional()->word(),
            'normalized_code' => Cabinet::normalizeCode($code),
        ];
    }

    /**
     * @param array{team_id: int} $attributes
     */
    private function servicePlanFactoryFor(array $attributes): ServicePlanFactory
    {
        return ServicePlan::factory()
            ->state(['team_id' => $attributes['team_id']]);
    }

    /**
     * @param array{member_id: int} $attributes
     */
    private function teamIdFor(array $attributes): int
    {
        $memberId = $attributes['member_id'];

        return Member::query()
            ->withTrashed()
            ->findOrFail($memberId)
            ->team_id;
    }
}
```
