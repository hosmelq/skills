# Index Tests: Live Record Pagination

Complete first-page index contract with sixteen live tenant records, foreign and soft deleted distractors, fifteen returned rows, from/to/total metadata, newest-created row first, derived finality false and an absent raw tenant foreign key.

This proves page one and its first row only; it does not test page-two traversal, every row field or a complete sort sequence.

## First Page

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Team;
use App\Models\WorkOrder;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('paginates live records with the newest record first', function (
    ): void {
        $team = Team::factory()->createOne();
        $workOrders = WorkOrder::factory()->count(16)->recycle($team)->create();
        $newest = $workOrders->last();
        WorkOrder::factory()->createOne();
        WorkOrder::factory()->trashed()->recycle($team)->createOne();

        login(team: $team);

        $response = get(route('teams.work-orders.index', $team));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($newest, $team): void {
                $page->component('work-orders/Index')
                    ->where('team.id', $team->sqid)
                    ->where('workOrders.meta.from', 1)
                    ->where('workOrders.meta.to', 15)
                    ->where('workOrders.meta.total', 16)
                    ->has('workOrders.data', 15)
                    ->where('workOrders.data.0.id', $newest->sqid)
                    ->where('workOrders.data.0.status.is_final', false)
                    ->missing('workOrders.data.0.team_id');
            });
    });
});
```
