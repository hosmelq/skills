# Database: Model Factory

Create the factory for a new model with every column in its definition, unique fake values where the schema requires them, values derived from one generated value, and named states for the lifecycle states tests set up.

- The model's `HasFactory` uses the same `Factory<Model>` generic.
- List every column except `id`, timestamps and `deleted_at`, with nullable ones as `null` or `fake()->optional()`. List a slug the model generates as `null`, and omit the sort column the sortable package assigns.
- Put foreign keys first, alphabetically with each derived key immediately after the key it reads, then a blank line, then the other columns alphabetically.
- Use `fake()->unique()` for columns behind unique indexes: `bothify('{Model} ######')` for names, and emails on a resolvable domain (`regexify('\w{8}@gmail\.com')`) when validation checks DNS.
- Assign a generated value to a local in the definition and derive dependent columns from it, such as a normalized code through the model's normalizer.
- Add a state for each named lifecycle state tests need; `trashed()` already exists for soft deletes.

```php
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
```
