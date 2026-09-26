# Controllers: Reject Deletion in a Final State

Root and child deletion catch the final-state exception but use different messages and destinations. Keep those response contracts. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\WorkOrders\DeleteWorkOrder;
use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Models\Team;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class WorkOrderController
{
    public function destroy(
        Team $team,
        WorkOrder $workOrder,
        DeleteWorkOrder $deleteWorkOrder,
    ): RedirectResponse {
        try {
            $deleteWorkOrder->handle($workOrder);
        } catch (WorkOrderIsFinal) {
            throw ValidationException::withMessages([
                'work_order' => __('work_order.validation.final_state'),
            ]);
        }

        return to_route('teams.work-orders.index', [
            'team' => $team,
        ])->toast(__('work_order.deleted.title'));
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\WorkOrderLines\DeleteWorkOrderLine;
use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class WorkOrderLineController
{
    public function destroy(
        Team $team,
        WorkOrder $workOrder,
        WorkOrderLine $line,
        DeleteWorkOrderLine $deleteWorkOrderLine,
    ): RedirectResponse {
        try {
            $deleteWorkOrderLine->handle($line);
        } catch (WorkOrderIsFinal) {
            throw ValidationException::withMessages([
                'work_order' => __('work_order_line.validation.final_delete'),
            ]);
        }

        return to_route('teams.work-orders.show', [
            'team' => $team,
            'work_order' => $workOrder,
        ])->toast(__('work_order_line.deleted.title'));
    }
}
```
