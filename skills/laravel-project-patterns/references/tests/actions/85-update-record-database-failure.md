# Action Tests: Propagate an Update Database Failure

Integration action tests: Inject an unrelated query failure during update and assert the exact exception object escapes unchanged.

```php
<?php

declare(strict_types=1);

use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Models\WorkOrder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('propagates an unrelated database failure', function (): void {
    $workOrder = WorkOrder::factory()->createOne();
    $expectedException = new QueryException(
        'pgsql',
        'update work_orders',
        [],
        new RuntimeException('Unrelated database failure.'),
    );
    $thrownException = null;

    DB::listen(function (QueryExecuted $query) use ($expectedException): void {
        throw_if(str_starts_with($query->sql, 'update "work_orders"'), $expectedException);
    });

    try {
        resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
            'note' => 'Trigger update',
        ]));
    } catch (QueryException $exception) {
        $thrownException = $exception;
    }

    expect($thrownException)->toBe($expectedException);
});
```
