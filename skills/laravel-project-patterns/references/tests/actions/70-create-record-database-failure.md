# Action Tests: Propagate a Create Database Failure

Integration action tests: Inject an unrelated query failure during insertion and assert the exact exception object escapes unchanged.

```php
<?php

declare(strict_types=1);

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('propagates an unrelated database failure', function (): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $expectedException = new QueryException(
        'pgsql',
        'insert into work_orders',
        [],
        new RuntimeException('Unrelated database failure.'),
    );
    $thrownException = null;

    DB::listen(function (QueryExecuted $query) use ($expectedException): void {
        throw_if(str_starts_with($query->sql, 'insert into "work_orders"'), $expectedException);
    });

    try {
        resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([]));
    } catch (QueryException $exception) {
        $thrownException = $exception;
    }

    expect($thrownException)->toBe($expectedException);
});
```
