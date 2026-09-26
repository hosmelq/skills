# Controllers: Return Ordered Collections

Use ordered()->get() for an unpaginated list. Include the owning enum options when the page consumes them. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\ItemGroupResource;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class ItemGroupController
{
    public function index(Team $team): Response
    {
        $itemGroups = $team->itemGroups()
            ->ordered()
            ->get();

        return Inertia::render('item-groups/Index', [
            'itemGroups' => ItemGroupResource::collection($itemGroups),
            'team' => $team->toResource(),
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\BaseStatus;
use App\Http\Resources\WorkOrderStatusResource;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderStatusController
{
    public function index(Team $team): Response
    {
        $workOrderStatuses = $team->workOrderStatuses()
            ->ordered()
            ->get();

        return Inertia::render('work-order-statuses/Index', [
            'baseStatuses' => BaseStatus::options(),
            'team' => $team->toResource(),
            'workOrderStatuses' => WorkOrderStatusResource::collection($workOrderStatuses),
        ]);
    }
}
```
