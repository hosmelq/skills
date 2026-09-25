# Action Tests: Translate an Update Index Conflict

Integration action tests: Use a second PostgreSQL connection to insert a competing reference after the update precheck; assert named-index exception translation and the competing row. This is controlled interleaving.

Use a dedicated PostgreSQL suite with committed fixtures and database truncation; an outer rollback transaction hides fixtures from the second connection. Isolate listeners/configuration per test and clean the competing row in `finally`. Match the inspected unique-index name and SQL.

```php
<?php

declare(strict_types=1);

use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Exceptions\WorkOrders\WorkOrderReferenceAlreadyExists;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

it('maps an interleaved reference index conflict', function (): void {
    $team = Team::factory()->createOne();
    $status = WorkOrderStatus::factory()->recycle($team)->createOne();
    $workOrder = WorkOrder::factory()->for($team)->for($status, 'workOrderStatus')->createOne();

    expect(DB::transactionLevel())->toBe(0);

    config()->set('database.connections.pgsql_competing', config('database.connections.pgsql'));
    DB::purge('pgsql_competing');
    $competingConnection = DB::connection('pgsql_competing');
    $insertedCompetingWorkOrder = false;

    DB::listen(function (QueryExecuted $query) use (
        $competingConnection,
        &$insertedCompetingWorkOrder,
        $status,
        $team,
    ): void {
        if ($insertedCompetingWorkOrder || ! str_contains($query->sql, 'lower(reference)')) {
            return;
        }

        $insertedCompetingWorkOrder = true;
        $competingConnection->table('work_orders')->insert([
            'created_at' => now(),
            'received_at' => now(),
            'reference' => 'RACE-UPDATE',
            'team_id' => $team->id,
            'updated_at' => now(),
            'work_order_status_id' => $status->id,
        ]);
    });

    try {
        expect(fn () => resolve(UpdateWorkOrder::class)->handle(
            $workOrder,
            UpdateWorkOrderInput::from([
                'reference' => 'race-update',
            ]),
        ))->toThrow(
            WorkOrderReferenceAlreadyExists::class,
            'The work order reference already exists.',
        );

        $competingWorkOrderExists = $competingConnection->table('work_orders')
            ->where('team_id', $team->id)
            ->where('reference', 'RACE-UPDATE')
            ->exists();

        expect($competingWorkOrderExists)->toBeTrue();
    } finally {
        $competingConnection->table('work_orders')
            ->where('team_id', $team->id)
            ->where('reference', 'RACE-UPDATE')
            ->delete();
        DB::disconnect('pgsql_competing');
    }
});
```
