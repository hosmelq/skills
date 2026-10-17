<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MembershipRole;
use App\Models\Clinic;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invitation>
 */
final class InvitationFactory extends Factory
{
    public function accepted(): static
    {
        return $this->state(['accepted_at' => now()]);
    }

    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'invited_by_user_id' => $this->invitedByUserIdFor(...),
            'accepted_at' => null,
            'email' => fake()->unique()->safeEmail(),
            'expires_at' => now()->addDays(7),
            'role' => MembershipRole::Staff,
            'token' => hash('sha256', str()->random(40)),
        ];
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subDay()]);
    }

    public function withToken(string $token): static
    {
        return $this->state(['token' => hash('sha256', $token)]);
    }

    /**
     * @param array{clinic_id: int} $attributes
     */
    private function invitedByUserIdFor(array $attributes): int
    {
        return Clinic::query()->findOrFail($attributes['clinic_id'])->owner_id;
    }
}
