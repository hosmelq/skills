# Controllers: Authorize Bound Controller Actions

Root creation can use a class-only ability. Parent-scoped class permissions receive the bound context; instance permissions receive the record. Preserve each operation mask and Sqid-decoding order; single-purpose routes need only their declared abilities. Method parameters need scoped route binding or an explicit ownership check.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MemberController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:create,'.Member::class.',team', only: ['create', 'store']),
            new Middleware('can:delete,member', only: ['destroy']),
            new Middleware('can:update,member', only: ['edit', 'update']),
            new Middleware('can:view,member', only: ['show']),
            new Middleware('can:viewAny,'.Member::class.',team', only: ['index']),
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MemberAddress;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MemberAddressController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:create,'.MemberAddress::class.',member', only: ['create', 'store']),
            new Middleware('can:delete,address', only: ['destroy']),
            new Middleware('can:update,address', only: ['edit', 'update']),
            new Middleware('can:view,address', only: ['show']),
            new Middleware('can:viewAny,'.MemberAddress::class.',member', only: ['index']),
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PlanRate;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PlanRateController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'can:create,'.PlanRate::class.',team,service_plan,plan_rule',
                only: ['create', 'store'],
            ),
            new Middleware('can:delete,rate', only: ['destroy']),
            new Middleware('can:update,rate', only: ['edit', 'update']),
            new Middleware('can:view,rate', only: ['show']),
            new Middleware(
                'can:viewAny,'.PlanRate::class.',team,service_plan,plan_rule',
                only: ['index'],
            ),
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class WorkOrderController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:create,'.WorkOrder::class.',team', only: ['create', 'store']),
            new Middleware('can:delete,work_order', only: ['destroy']),
            new Middleware('can:update,work_order', only: ['edit', 'update']),
            new Middleware('can:view,work_order', only: ['show']),
            new Middleware('can:viewAny,'.WorkOrder::class.',team', only: ['index']),
            new Middleware(
                'sqids:current_facility_id,member_id,cabinet_id,work_order_status_id,'.
                'pickup_facility_id,received_facility_id,service_plan_id,'.
                'plan_rule_id',
                only: ['store', 'update'],
            ),
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TeamController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:create,'.Team::class, only: ['store']),
            new Middleware('can:update,team', only: ['update']),
            new Middleware('can:view,team', only: ['show']),
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Cabinet;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CabinetController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:create,'.Cabinet::class.',member', only: ['create', 'store']),
            new Middleware('can:delete,cabinet', only: ['destroy']),
            new Middleware('can:update,cabinet', only: ['edit', 'update']),
            new Middleware('can:view,cabinet', only: ['show']),
            new Middleware('can:viewAny,'.Cabinet::class.',member', only: ['index']),
            new Middleware('sqids:service_plan_id', only: ['store']),
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\WorkOrderLine;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class WorkOrderLineController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'can:create,'.WorkOrderLine::class.',work_order',
                only: ['create', 'store'],
            ),
            new Middleware('can:delete,line', only: ['destroy']),
            new Middleware('can:update,line', only: ['edit', 'update']),
            new Middleware('can:view,line', only: ['show']),
            new Middleware('sqids:item_group_id', only: ['store', 'update']),
        ];
    }
}
```
