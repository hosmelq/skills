# Database: Range Exclusion Constraints

Prevent active records from overlapping ranges within the same parent with a PostgreSQL GiST exclusion constraint, open-ended ranges included, and test overlaps, adjacency and soft-deleted rows.

- The `btree_gist` extension migration must run first.
- Build the range with the function for the column type (`numrange`, `tsrange`, `daterange`) and `[)` bounds.
- Name the constraint `{table}_active_range_exclusion`.
- Give the factory a range state and an open-ended state, deriving the tenant from the ranged parent. Constraint tests live in the model's integration test file and assert both range columns on accepted rows.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('boarding_stays', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('clinic_id')->index();
            $table->foreignId('pet_id')->index();
            $table->foreignId('room_id')->index();

            $table->timestamp('ends_at')->nullable();
            $table->timestamp('starts_at');

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement(<<<'SQL'
                ALTER TABLE boarding_stays
                ADD CONSTRAINT boarding_stays_active_range_exclusion
                EXCLUDE USING gist (
                    room_id WITH =,
                    tsrange(starts_at, ends_at, '[)') WITH &&
                )
                WHERE (deleted_at IS NULL)
            SQL);
    }
};
```

```php
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
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Models\BoardingStay;
use Illuminate\Database\QueryException;

it('rejects active overlapping stays', function (): void {
    $stay = BoardingStay::factory()->forRange(now(), now()->addDays(5))->createOne();

    expect(fn () => BoardingStay::factory()
        ->for($stay->room)
        ->forRange(now()->addDays(4), now()->addDays(6))
        ->createOne())
        ->toThrow(QueryException::class, 'boarding_stays_active_range_exclusion');
});

it('allows adjacent active stays', function (): void {
    $stay = BoardingStay::factory()->forRange(now(), now()->addDays(5))->createOne();
    $adjacentStay = BoardingStay::factory()
        ->for($stay->room)
        ->forRange(now()->addDays(5), now()->addDays(10))
        ->createOne();

    assertDatabaseHas(BoardingStay::class, [
        'ends_at' => now()->addDays(10),
        'id' => $adjacentStay->id,
        'room_id' => $stay->room_id,
        'starts_at' => now()->addDays(5),
    ]);
});

it('ignores soft deleted stays', function (): void {
    $stay = BoardingStay::factory()->trashed()->forRange(now(), now()->addDays(5))->createOne();
    $replacementStay = BoardingStay::factory()
        ->for($stay->room)
        ->forRange(now(), now()->addDays(5))
        ->createOne();

    assertDatabaseHas(BoardingStay::class, [
        'ends_at' => now()->addDays(5),
        'id' => $replacementStay->id,
        'room_id' => $stay->room_id,
        'starts_at' => now(),
    ]);
});
```
