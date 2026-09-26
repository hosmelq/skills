# Controllers: Expose Nested Form Options and Mutation State

Viewable child pages derive canMutate from the historical parent state. Edit keeps the selected deactivated group in the ordered options; normal soft-delete scopes still apply. This flag does not replace action guards. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Http\Inertia\WorkOrderLines\ItemGroupResources;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderLineController
{
    public function create(Team $team, WorkOrder $workOrder): Response
    {
        return Inertia::render('work-orders/lines/Create', [
            'canMutate' => $this->canMutate($workOrder),
            'currencies' => CurrencyCode::options(),
            'itemGroups' => new ItemGroupResources($workOrder),
            'lengthUnits' => LengthUnit::options(),
            'team' => $team->toResource(),
            'weightUnits' => WeightUnit::options(),
            'workOrder' => $workOrder->toResource(),
        ]);
    }

    public function edit(Team $team, WorkOrder $workOrder, WorkOrderLine $line): Response
    {
        $line->load('itemGroup');

        return Inertia::render('work-orders/lines/Edit', [
            'canMutate' => $this->canMutate($workOrder),
            'currencies' => CurrencyCode::options(),
            'itemGroups' => new ItemGroupResources($workOrder, $line->itemGroup),
            'lengthUnits' => LengthUnit::options(),
            'line' => $line->toResource(),
            'team' => $team->toResource(),
            'weightUnits' => WeightUnit::options(),
            'workOrder' => $workOrder->toResource(),
        ]);
    }

    public function show(Team $team, WorkOrder $workOrder, WorkOrderLine $line): Response
    {
        $line->load('itemGroup');

        return Inertia::render('work-orders/lines/Show', [
            'canMutate' => $this->canMutate($workOrder),
            'line' => $line->toResource(),
            'team' => $team->toResource(),
            'workOrder' => $workOrder->toResource(),
        ]);
    }

    private function canMutate(WorkOrder $workOrder): bool
    {
        return ! $workOrder->workOrderStatus()
            ->withTrashed()
            ->firstOrFail()
            ->base_status
            ->isFinal();
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Inertia\WorkOrderLines;

use App\Http\Resources\ItemGroupResource;
use App\Models\ItemGroup;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Override;

class ItemGroupResources implements ProvidesInertiaProperty
{
    public function __construct(
        private readonly WorkOrder $workOrder,
        private readonly null|ItemGroup $currentItemGroup = null,
    ) {
    }

    #[Override]
    public function toInertiaProperty(PropertyContext $context): AnonymousResourceCollection
    {
        $itemGroups = $this->workOrder->team->itemGroups()
            ->where(function (Builder $builder): void {
                $builder->whereNull('deactivated_at');

                if ($this->currentItemGroup instanceof ItemGroup) {
                    $builder->orWhere('id', $this->currentItemGroup->id);
                }
            })
            ->ordered()
            ->get();

        return ItemGroupResource::collection($itemGroups);
    }
}
```
