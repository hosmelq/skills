# Database: Factory Derived Parents

Keep factory-created records consistent with their parent's tenant by deriving the tenant key, or a sibling parent, from the parent the definition creates, so tests that override one parent never mix tenants.

- Derive a key with a private method passed as a first-class callable; it receives the attributes resolved so far, so it comes right after the key it reads.
- Load a soft-deleting parent `withTrashed()` so trashed fixtures still resolve.
- Return a factory with the derived tenant when the definition must create another parent in the same tenant.
- Tests then pass the parent with `recycle()` or `for()` and never set the derived key.

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Pet;
use App\Models\Room;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visit>
 */
final class VisitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'attending_veterinarian_id' => User::factory(),
            'pet_id' => Pet::factory(),
            'clinic_id' => $this->clinicIdFor(...),
            'room_id' => $this->roomFactoryFor(...),
            'referring_veterinarian_id' => null,

            'notes' => fake()->optional()->paragraph(),
            'scheduled_at' => now()->addDay(),
        ];
    }

    /**
     * @param array{pet_id: int} $attributes
     */
    private function clinicIdFor(array $attributes): int
    {
        $petId = $attributes['pet_id'];

        return Pet::query()->withTrashed()->findOrFail($petId)->clinic_id;
    }

    /**
     * @param array{clinic_id: int} $attributes
     */
    private function roomFactoryFor(array $attributes): RoomFactory
    {
        return Room::factory()->state(['clinic_id' => $attributes['clinic_id']]);
    }
}
```
