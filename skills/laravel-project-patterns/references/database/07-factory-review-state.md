# Factories: Configure Review State

The approved state changes only status. Rejection supplies both the reviewer and review time; a supplied user is reused, otherwise its factory is expanded. The team lookup includes deleted members.

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    public function approved(): static
    {
        return $this->state([
            'status' => EnrollmentStatus::Approved,
        ]);
    }

    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'team_id' => $this->teamIdFor(...),
            'requested_by_user_id' => User::factory(),
            'reviewed_by_user_id' => null,

            'requested_at' => now(),
            'reviewed_at' => null,
            'status' => EnrollmentStatus::Pending,
        ];
    }

    public function rejected(null|User $reviewedByUser = null): static
    {
        return $this->state([
            'reviewed_at' => now(),
            'reviewed_by_user_id' => $reviewedByUser ?? User::factory(),
            'status' => EnrollmentStatus::Rejected,
        ]);
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
