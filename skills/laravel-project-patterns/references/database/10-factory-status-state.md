# Factories: Configure Status Flags

Treat visibility, deactivation, initial selection and base status as separate states. The initial state resets deactivation and selects the permitted base status; it does not change visibility.

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrderStatus>
 */
class WorkOrderStatusFactory extends Factory
{
    public function deactivated(): static
    {
        return $this->state(['deactivated_at' => now()]);
    }

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),

            'base_status' => BaseStatus::Open,
            'color' => fake()->hexColor(),
            'deactivated_at' => null,
            'description' => fake()->optional()->paragraph(),
            'is_initial' => false,
            'is_member_visible' => false,
            'name' => fake()->unique()->bothify('Status ######'),
        ];
    }

    public function initial(): static
    {
        return $this->state([
            'base_status' => BaseStatus::Open,
            'deactivated_at' => null,
            'is_initial' => true,
        ]);
    }

    public function memberVisible(): static
    {
        return $this->state(['is_member_visible' => true]);
    }

    public function withBaseStatus(BaseStatus $baseStatus): static
    {
        return $this->state(['base_status' => $baseStatus]);
    }
}
```
