# Controllers: Render Parent and Ancestor Context

Keep every bound ancestor and record resource consumed by the nested page. A child can have a show page even when its writes redirect to the parent list. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class PlanRateController
{
    public function create(Team $team, ServicePlan $servicePlan, PlanRule $planRule): Response
    {
        return Inertia::render('service-plans/rates/Create', [
            'planRule' => $planRule->toResource(),
            'servicePlan' => $servicePlan->toResource(),
            'team' => $team->toResource(),
        ]);
    }

    public function edit(
        Team $team,
        ServicePlan $servicePlan,
        PlanRule $planRule,
        PlanRate $rate
    ): Response {
        return Inertia::render('service-plans/rates/Edit', [
            'planRule' => $planRule->toResource(),
            'rate' => $rate->toResource(),
            'servicePlan' => $servicePlan->toResource(),
            'team' => $team->toResource(),
        ]);
    }

    public function show(
        Team $team,
        ServicePlan $servicePlan,
        PlanRule $planRule,
        PlanRate $rate
    ): Response {
        return Inertia::render('service-plans/rates/Show', [
            'planRule' => $planRule->toResource(),
            'rate' => $rate->toResource(),
            'servicePlan' => $servicePlan->toResource(),
            'team' => $team->toResource(),
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberAddress;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class MemberAddressController
{
    public function show(Team $team, Member $member, MemberAddress $address): Response
    {
        return Inertia::render('members/addresses/Show', [
            'address' => $address->toResource(),
            'member' => $member->toResource(),
            'team' => $team->toResource(),
        ]);
    }
}
```
