<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
final class RoomFactory extends Factory
{
    public function deactivated(): static
    {
        return $this->state(['deactivated_at' => now()]);
    }

    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),

            'capacity' => fake()->numberBetween(1, 4),
            'code' => $code = fake()->unique()->numerify('EX-######'),
            'deactivated_at' => null,
            'name' => fake()->unique()->bothify('Room ######'),
            'normalized_code' => Room::normalizeCode($code),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
