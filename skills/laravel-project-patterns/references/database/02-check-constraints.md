# Database: Check Constraints

Reject invalid column values in the database with named check constraints added after the table is created, whether they bound one column, require a group of columns together or tie one column to another, and test the values each constraint rejects.

- PostgreSQL Enhanced has no table check constraints: add each with `DB::statement()` after `Schema::create()`, named `{table}_{rule}_check`; single-column checks come first.
- A range check on a nullable column lets `NULL` pass explicitly (`column IS NULL OR column BETWEEN …`).
- A group of optional columns is either entirely null or entirely set with valid values; express both sides in one constraint and give the factory a state that sets the group together.
- Constraint tests live in the model's integration test file and assert the violation last.

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
        Schema::create('pets', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('client_id')->index();
            $table->foreignId('clinic_id')->index();

            $table->unsignedSmallInteger('body_condition_score')->nullable();
            $table->timestamp('deceased_at')->nullable();
            $table->text('deceased_reason')->nullable();
            $table->caseInsensitiveText('name');
            $table->timestamp('registered_at')->nullable();
            $table->decimal('weight', 8, 4)->nullable();
            $table->string('weight_unit')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE pets
            ADD CONSTRAINT pets_body_condition_score_range_check
            CHECK (body_condition_score IS NULL OR body_condition_score BETWEEN 1 AND 9)
            SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE pets
            ADD CONSTRAINT pets_deceased_reason_check
            CHECK (deceased_reason IS NULL OR deceased_at IS NOT NULL)
            SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE pets
            ADD CONSTRAINT pets_weight_pair_check
            CHECK (
                (weight IS NULL AND weight_unit IS NULL)
                OR (weight IS NOT NULL AND weight > 0 AND weight_unit IS NOT NULL)
            )
            SQL);
    }
};
```

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WeightUnit;
use App\Models\Client;
use App\Models\Clinic;
use App\Models\Pet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pet>
 */
final class PetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'client_id' => $this->clientFactoryFor(...),

            'body_condition_score' => null,
            'deceased_at' => null,
            'deceased_reason' => null,
            'name' => fake()->firstName(),
            'registered_at' => null,
            'weight' => null,
            'weight_unit' => null,
        ];
    }

    public function withWeight(WeightUnit $weightUnit = WeightUnit::Kilograms): static
    {
        return $this->state([
            'weight' => 1,
            'weight_unit' => $weightUnit,
        ]);
    }

    /**
     * @param array{clinic_id: int} $attributes
     */
    private function clientFactoryFor(array $attributes): ClientFactory
    {
        return Client::factory()->state(['clinic_id' => $attributes['clinic_id']]);
    }
}
```

```php
<?php

declare(strict_types=1);
use App\Models\Pet;
use Illuminate\Database\QueryException;

it('enforces the body condition score range at the database level', function (): void {
    expect(fn () => Pet::factory()->createOne(['body_condition_score' => 10]))
        ->toThrow(QueryException::class, 'pets_body_condition_score_range_check');
});

it('requires a death date for a deceased reason at the database level', function (): void {
    expect(fn () => Pet::factory()->createOne([
        'deceased_at' => null,
        'deceased_reason' => 'Illness',
    ]))->toThrow(QueryException::class, 'pets_deceased_reason_check');
});
```
