<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VisitStage;
use App\Models\Clinic;
use App\Models\VisitStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisitStatus>
 */
final class VisitStatusFactory extends Factory
{
    public function deactivated(): static
    {
        return $this->state(['deactivated_at' => now()]);
    }

    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'deactivated_at' => null,
            'is_initial' => false,
            'name' => fake()->unique()->bothify('Visit Status ######'),
            'stage' => VisitStage::CheckedIn,
        ];
    }

    public function initial(): static
    {
        return $this->state(['deactivated_at' => null, 'is_initial' => true, 'stage' => VisitStage::CheckedIn]);
    }
}
