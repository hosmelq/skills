# Controllers: Show Scoped Children and Historical Relations

Filter children by tenant, eager-load the group, order oldest IDs and count the loaded collection. Load historical relations on the parent independently. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\WorkOrderLineResource;
use App\Models\Team;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderController
{
    public function show(Team $team, WorkOrder $workOrder): Response
    {
        $lines = $workOrder->lines()
            ->where('team_id', $team->id)
            ->with('itemGroup')
            ->oldest('id')
            ->get();

        return Inertia::render('work-orders/Show', [
            'lineCount' => $lines->count(),
            'lines' => WorkOrderLineResource::collection($lines),
            'team' => $team->toResource(),
            'workOrder' => $workOrder->load([
                'cabinet' => fn (Relation $relation): Builder => $relation->getQuery()
                    ->withoutGlobalScope(SoftDeletingScope::class),
                'currentFacility' => fn (Relation $relation): Builder => $relation->getQuery()
                    ->withoutGlobalScope(SoftDeletingScope::class),
                'member' => fn (Relation $relation): Builder => $relation->getQuery()
                    ->withoutGlobalScope(SoftDeletingScope::class),
                'pickupFacility' => fn (Relation $relation): Builder => $relation->getQuery()
                    ->withoutGlobalScope(SoftDeletingScope::class),
                'planRule' => fn (Relation $relation): Builder => $relation->getQuery()
                    ->withoutGlobalScope(SoftDeletingScope::class),
                'receivedFacility' => fn (Relation $relation): Builder => $relation->getQuery()
                    ->withoutGlobalScope(SoftDeletingScope::class),
                'servicePlan' => fn (Relation $relation): Builder => $relation->getQuery()
                    ->withoutGlobalScope(SoftDeletingScope::class),
                'workOrderStatus' => fn (Relation $relation): Builder => $relation->getQuery()
                    ->withoutGlobalScope(SoftDeletingScope::class),
            ])->toResource(),
        ]);
    }
}
```
