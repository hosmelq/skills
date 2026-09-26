# Controllers: Keep Historical Relations in a List

Remove only the soft-delete scope from the named related queries. The primary records retain their normal scopes and latest-ID pagination. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderController
{
    public function index(Team $team): Response
    {
        $workOrders = $team->workOrders()
            ->with([
                'member' => fn (Relation $relation): Builder => $relation->getQuery()
                    ->withoutGlobalScope(SoftDeletingScope::class),
                'workOrderStatus' => fn (Relation $relation): Builder => $relation->getQuery()
                    ->withoutGlobalScope(SoftDeletingScope::class),
            ])
            ->latest('id')
            ->paginate();

        return Inertia::render('work-orders/Index', [
            'team' => $team->toResource(),
            'workOrders' => $workOrders->toResourceCollection(),
        ]);
    }
}
```
