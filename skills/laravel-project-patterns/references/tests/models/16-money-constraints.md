# Model Tests: Value and Currency Constraints

Database checks require amount and currency together and reject negative amounts; zero with a currency is valid and persisted.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Enums\CurrencyCode;
use App\Models\WorkOrderLine;
use Illuminate\Database\QueryException;

it('enforces the value and currency pair at the database level', function (array $attributes): void {
    expect(fn () => WorkOrderLine::factory()->createOne($attributes))
        ->toThrow(QueryException::class, 'work_order_lines_value_currency_check');
})->with([
    'value without currency' => [[
        'currency_code' => null,
        'unit_value' => 1,
    ]],
    'currency without value' => [[
        'currency_code' => CurrencyCode::USD,
        'unit_value' => null,
    ]],
    'negative value' => [[
        'currency_code' => CurrencyCode::USD,
        'unit_value' => -1,
    ]],
]);

it('allows a zero value with a currency', function (): void {
    $workOrderLine = WorkOrderLine::factory()->createOne([
        'currency_code' => CurrencyCode::USD,
        'unit_value' => 0,
    ]);

    assertDatabaseHas(WorkOrderLine::class, [
        'id' => $workOrderLine->id,
        'currency_code' => CurrencyCode::USD,
        'unit_value' => 0,
    ]);
});
```
