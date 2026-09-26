# Controllers: Load Historical Relations for Editing

Expose the bound record with the named historical relations while the separate form-property group supplies selectable options. Keep relation roles distinct. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Inertia\WorkOrders\WorkOrderFormProps;
use App\Models\Team;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderController
{
    public function edit(Team $team, WorkOrder $workOrder): Response
    {
        return Inertia::render('work-orders/Edit', [
            new WorkOrderFormProps($team),
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
