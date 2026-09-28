# Factories: Create a Child Record

Use `afterCreating()` when the child needs the persisted parent. Optional Faker values remain nullable; this hook creates one address marked as the default.

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Member;
use App\Models\MemberAddress;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),

            'email' => fake()->unique()->regexify('\w{8}@gmail\.com'),
            'first_name' => fake()->optional()->firstName(),
            'last_name' => fake()->optional()->lastName(),
            'note' => fake()->optional()->paragraph(),
            'phone_number' => fake()->optional()->passthrough(
                fake()->unique()->numerify('+1 415 555 ####'),
            ),
        ];
    }

    public function withDefaultAddress(): static
    {
        return $this->afterCreating(function (Member $member): void {
            MemberAddress::factory()->for($member)->createOne(['is_default' => true]);
        });
    }
}
```
