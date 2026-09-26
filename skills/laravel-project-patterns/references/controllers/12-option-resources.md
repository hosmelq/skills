# Controllers: Return Resources as Options

Use the property provider when the form consumes full resources. Query active records in the tenant and order by name. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Inertia\ServicePlans\ActiveServicePlanResources;
use App\Models\Member;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class CabinetController
{
    public function create(Team $team, Member $member): Response
    {
        return Inertia::render('members/cabinets/Create', [
            'member' => $member->toResource(),
            'servicePlans' => new ActiveServicePlanResources($team),
            'team' => $team->toResource(),
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Inertia\ServicePlans;

use App\Http\Resources\ServicePlanResource;
use App\Models\Team;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Override;

class ActiveServicePlanResources implements ProvidesInertiaProperty
{
    public function __construct(private readonly Team $team)
    {
    }

    #[Override]
    public function toInertiaProperty(PropertyContext $context): AnonymousResourceCollection
    {
        $servicePlans = $this->team->servicePlans()
            ->active()
            ->orderBy('name')
            ->get();

        return ServicePlanResource::collection($servicePlans);
    }
}
```
