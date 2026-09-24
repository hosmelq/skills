# Model Tests: Range Exclusion Constraints

Database exclusion constraint for overlapping active ranges: bounded insert, a second open range, conflicting update, adjacent endpoints, soft-deleted reuse and different-parent isolation.

Use the configured engine and real exclusion constraint. The database constraint uses lower-inclusive, upper-exclusive ranges; `forRange()` sets the endpoint fields. Null is an open upper bound. Adjacent rows are valid.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Models\PlanRate;
use Illuminate\Database\QueryException;

it('rejects overlapping active ranges', function (): void {
    $rate = PlanRate::factory()
        ->forRange(0, 5)
        ->createOne();

    expect(fn () => PlanRate::factory()
        ->recycle($rate->planRule)
        ->forRange(4, 6)
        ->createOne())
        ->toThrow(QueryException::class, 'plan_rates_active_range_exclusion');
});

it('rejects multiple active open-ended ranges', function (): void {
    $rate = PlanRate::factory()
        ->forRange(0, null)
        ->createOne();

    expect(fn () => PlanRate::factory()
        ->recycle($rate->planRule)
        ->forRange(10, null)
        ->createOne())
        ->toThrow(QueryException::class, 'plan_rates_active_range_exclusion');
});

it('rejects updates that create overlapping ranges', function (): void {
    $rate = PlanRate::factory()
        ->forRange(0, 5)
        ->createOne();
    $otherRate = PlanRate::factory()
        ->recycle($rate->planRule)
        ->forRange(5, 10)
        ->createOne();

    expect(fn () => $otherRate->update([
        'minimum_weight' => 4,
    ]))->toThrow(QueryException::class, 'plan_rates_active_range_exclusion');
});

it('allows adjacent active ranges', function (): void {
    $rate = PlanRate::factory()
        ->forRange(0, 5)
        ->createOne();

    $adjacentRate = PlanRate::factory()
        ->recycle($rate->planRule)
        ->forRange(5, 10)
        ->createOne();

    assertDatabaseHas(PlanRate::class, [
        'id' => $adjacentRate->id,
        'maximum_weight' => '10.0000',
        'minimum_weight' => '5.0000',
        'plan_rule_id' => $rate->plan_rule_id,
    ]);
});

it('ignores soft-deleted ranges', function (): void {
    $rate = PlanRate::factory()
        ->trashed()
        ->forRange(0, 5)
        ->createOne();

    $replacementRate = PlanRate::factory()
        ->recycle($rate->planRule)
        ->forRange(0, 5)
        ->createOne();

    assertDatabaseHas(PlanRate::class, [
        'id' => $replacementRate->id,
        'maximum_weight' => '5.0000',
        'minimum_weight' => '0.0000',
        'plan_rule_id' => $rate->plan_rule_id,
    ]);
});

it('isolates overlapping ranges between parents', function (): void {
    $rate = PlanRate::factory()
        ->forRange(0, 5)
        ->createOne();

    $otherRate = PlanRate::factory()
        ->forRange(0, 5)
        ->createOne();

    assertDatabaseHas(PlanRate::class, [
        'id' => $otherRate->id,
        'maximum_weight' => '5.0000',
        'minimum_weight' => '0.0000',
        'plan_rule_id' => $otherRate->plan_rule_id,
    ]);
});
```
