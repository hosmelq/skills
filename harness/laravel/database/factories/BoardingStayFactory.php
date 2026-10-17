<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BoardingStay;
use App\Models\Pet;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BoardingStay>
 */
final class BoardingStayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'clinic_id' => $this->clinicIdFor(...),
            'pet_id' => $this->petFactoryFor(...),

            'ends_at' => now()->addDays(3),
            'starts_at' => now(),
        ];
    }

    public function forRange(CarbonImmutable $startsAt, null|CarbonImmutable $endsAt): static
    {
        return $this->state([
            'ends_at' => $endsAt,
            'starts_at' => $startsAt,
        ]);
    }

    public function openEnded(): static
    {
        return $this->state(['ends_at' => null]);
    }

    /**
     * @param array{room_id: int} $attributes
     */
    private function clinicIdFor(array $attributes): int
    {
        $roomId = $attributes['room_id'];

        return Room::query()->findOrFail($roomId)->clinic_id;
    }

    /**
     * @param array{clinic_id: int} $attributes
     */
    private function petFactoryFor(array $attributes): PetFactory
    {
        return Pet::factory()->state(['clinic_id' => $attributes['clinic_id']]);
    }
}
