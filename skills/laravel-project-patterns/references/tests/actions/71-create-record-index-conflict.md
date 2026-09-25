# Action Tests: Translate an Insert Index Conflict

Integration action tests: Use a second PostgreSQL connection to insert a competing active reference after the precheck; assert named-index exception translation and the competing row. This is controlled interleaving.

Use a dedicated PostgreSQL suite with committed fixtures and database truncation; an outer rollback transaction hides fixtures from the second connection. Isolate listeners/configuration per test and clean the competing row in `finally`. Match the inspected unique-index name and SQL.

```php
<?php

declare(strict_types=1);

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Exceptions\WorkOrders\WorkOrderReferenceAlreadyExists;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

it('maps an interleaved reference index conflict', function (): void {
    $team = Team::factory()->createOne();
    $status = WorkOrderStatus::factory()->initial()->recycle($team)->createOne();

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
            'reference' => 'RACE-REF',
            'team_id' => $team->id,
            'updated_at' => now(),
            'work_order_status_id' => $status->id,
        ]);
    });

    try {
        expect(fn () => resolve(CreateWorkOrder::class)->handle(
            $team,
            CreateWorkOrderInput::from([
                'reference' => 'race-ref',
            ]),
        ))->toThrow(
            WorkOrderReferenceAlreadyExists::class,
            'The work order reference already exists.',
        )
            ->and($competingConnection->table('work_orders')
                ->where('team_id', $team->id)
                ->where('reference', 'RACE-REF')->exists())->toBeTrue();
    } finally {
        $competingConnection->table('work_orders')
            ->where('team_id', $team->id)
            ->where('reference', 'RACE-REF')
            ->delete();
        DB::disconnect('pgsql_competing');
    }
});
```
