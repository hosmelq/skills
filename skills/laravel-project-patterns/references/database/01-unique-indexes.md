# Database: Partial Unique Indexes

Keep values unique per tenant among records that are not soft deleted, case-insensitively when people type them, or allow only one flagged record per parent, with named partial unique indexes and integration tests for their violations.

- Name indexes `{table}_active_{rule}_unique`, where `{rule}` names what stays unique without the scoping key and without `_id`, `_code` or `is_` affixes, and assert that name through `UniqueConstraintViolationException::$index`. Keep names within PostgreSQL's 63 characters.
- Exclude soft-deleted rows with a `where` callback. Values people choose, like names and codes, stay reserved while deactivated; an index that allows one flagged record also leaves deactivated rows out.
- Compare case-insensitively with a `caseInsensitiveText()` column; the `citext` extension migration must run first. Use a raw `where` string when the condition combines several columns.
- Values unique across the whole table, like tokens and slugs, use the column's `unique()`. A pair unique for the whole table without soft deletes is named `{table}_{first}_{second}_unique`; pivot tables keep Laravel's default name.
- A nullable text column unique without case and without `citext` indexes `(lower(column))` with `->where('column IS NOT NULL AND deleted_at IS NULL')` and tests that several nulls are allowed.
- Values the model normalizes, like codes and E.164 phone numbers, are tested by inserting a differently formatted variant of an existing value.
- Constraint tests live in the model's integration test file. Assert the violation last in a test, because PostgreSQL aborts the test transaction after it.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('visit_statuses', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('clinic_id')->index();

            $table->timestamp('deactivated_at')->nullable();
            $table->boolean('is_initial')->default(false);
            $table->caseInsensitiveText('name');
            $table->unsignedInteger('sort_order');
            $table->string('stage');

            $table->timestamps();
            $table->softDeletes();

            $table->uniqueIndex('clinic_id', 'visit_statuses_active_initial_unique')
                ->where('is_initial = true AND deactivated_at IS NULL AND deleted_at IS NULL');
            $table->uniqueIndex(['clinic_id', 'name'], 'visit_statuses_active_name_unique')
                ->where(fn (Builder $query): Builder => $query->whereNull('deleted_at'));
        });
    }
};
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Models\Clinic;
use App\Models\VisitStatus;
use Illuminate\Database\UniqueConstraintViolationException;

it('enforces case-insensitive name uniqueness per clinic at the database level', function (): void {
    $visitStatus = VisitStatus::factory()->createOne([
        'name' => 'Checked in',
    ]);
    $otherClinic = Clinic::factory()->createOne();

    VisitStatus::factory()->recycle($otherClinic)->createOne([
        'name' => 'checked in',
    ]);

    assertDatabaseHas(VisitStatus::class, [
        'clinic_id' => $otherClinic->id,
        'name' => 'checked in',
    ]);

    expect(fn () => VisitStatus::factory()->recycle($visitStatus->clinic)->createOne([
        'name' => 'checked in',
    ]))->toThrow(function (UniqueConstraintViolationException $exception): void {
        expect($exception->index)->toBe('visit_statuses_active_name_unique');
    });
});

it('allows only one active initial status per clinic at the database level', function (): void {
    $visitStatus = VisitStatus::factory()->initial()->createOne();

    expect(fn () => VisitStatus::factory()->initial()->recycle($visitStatus->clinic)->createOne([
        'name' => 'Second initial',
    ]))->toThrow(function (UniqueConstraintViolationException $exception): void {
        expect($exception->index)->toBe('visit_statuses_active_initial_unique');
    });
});
```
