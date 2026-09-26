# Controllers: Map Nested Write Errors

Creation and update preserve their distinct final-state messages and the unavailable-group field error. Redirect to the created or bound child under its parent. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\WorkOrderLines\CreateWorkOrderLine;
use App\Actions\WorkOrderLines\Inputs\CreateWorkOrderLineInput;
use App\Actions\WorkOrderLines\Inputs\UpdateWorkOrderLineInput;
use App\Actions\WorkOrderLines\UpdateWorkOrderLine;
use App\Exceptions\WorkOrderLines\ItemGroupIsUnavailable;
use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Http\Requests\StoreWorkOrderLineRequest;
use App\Http\Requests\UpdateWorkOrderLineRequest;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class WorkOrderLineController
{
    public function store(
        StoreWorkOrderLineRequest $request,
        Team $team,
        WorkOrder $workOrder,
        CreateWorkOrderLine $createWorkOrderLine,
    ): RedirectResponse {
        try {
            $line = $createWorkOrderLine->handle(
                $workOrder,
                CreateWorkOrderLineInput::from($request->validated()),
            );
        } catch (WorkOrderIsFinal) {
            throw ValidationException::withMessages([
                'work_order' => __('work_order_line.validation.final_create'),
            ]);
        } catch (ItemGroupIsUnavailable) {
            throw ValidationException::withMessages([
                'item_group_id' => __('work_order_line.validation.category_unavailable'),
            ]);
        }

        return to_route('teams.work-orders.lines.show', [
            'line' => $line,
            'team' => $team,
            'work_order' => $workOrder,
        ])->toast(__('work_order_line.created.title'));
    }

    public function update(
        UpdateWorkOrderLineRequest $request,
        Team $team,
        WorkOrder $workOrder,
        WorkOrderLine $line,
        UpdateWorkOrderLine $updateWorkOrderLine,
    ): RedirectResponse {
        try {
            $updateWorkOrderLine->handle(
                $line,
                UpdateWorkOrderLineInput::from($request->validated()),
            );
        } catch (WorkOrderIsFinal) {
            throw ValidationException::withMessages([
                'work_order' => __('work_order_line.validation.final_update'),
            ]);
        } catch (ItemGroupIsUnavailable) {
            throw ValidationException::withMessages([
                'item_group_id' => __('work_order_line.validation.category_unavailable'),
            ]);
        }

        return to_route('teams.work-orders.lines.show', [
            'line' => $line,
            'team' => $team,
            'work_order' => $workOrder,
        ])->toast(__('work_order_line.updated.title'));
    }
}
```
