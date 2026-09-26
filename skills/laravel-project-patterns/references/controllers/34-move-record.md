# Controllers: Resolve an Optional Predecessor

Omitted or null predecessor means the start. Resolve nonnull IDs under the tenant; the grouped variant also constrains base status before firstOrFail. Decode Sqids before validation.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\ItemGroups\MoveItemGroup;
use App\Http\Requests\MoveItemGroupRequest;
use App\Models\ItemGroup;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MoveItemGroupController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:update,item_group'),
            new Middleware('sqids:move_after_id'),
        ];
    }

    public function __invoke(
        MoveItemGroupRequest $request,
        Team $team,
        ItemGroup $itemGroup,
        MoveItemGroup $moveItemGroup,
    ): RedirectResponse {
        /** @var array{move_after_id?: null|int} $validated */
        $validated = $request->validated();

        $moveAfterItemGroup = null;

        if (($validated['move_after_id'] ?? null) !== null) {
            $moveAfterItemGroup = $team->itemGroups()
                ->whereKey($validated['move_after_id'])
                ->firstOrFail();
        }

        $moveItemGroup->handle($itemGroup, $moveAfterItemGroup);

        return back()->toast(__('item_group.moved.title'));
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\WorkOrderStatuses\MoveWorkOrderStatus;
use App\Http\Requests\MoveWorkOrderStatusRequest;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MoveWorkOrderStatusController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:update,work_order_status'),
            new Middleware('sqids:move_after_id'),
        ];
    }

    public function __invoke(
        MoveWorkOrderStatusRequest $request,
        Team $team,
        WorkOrderStatus $workOrderStatus,
        MoveWorkOrderStatus $moveWorkOrderStatus,
    ): RedirectResponse {
        /** @var array{move_after_id?: null|int} $validated */
        $validated = $request->validated();

        $moveAfterWorkOrderStatus = null;

        if (($validated['move_after_id'] ?? null) !== null) {
            $moveAfterWorkOrderStatus = $team->workOrderStatuses()
                ->where('base_status', $workOrderStatus->base_status)
                ->whereKey($validated['move_after_id'])
                ->firstOrFail();
        }

        $moveWorkOrderStatus->handle($workOrderStatus, $moveAfterWorkOrderStatus);

        return back()->toast(__('work_order_status.moved.title'));
    }
}
```
