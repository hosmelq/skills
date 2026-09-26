# Controllers: Render Enum Options and Edit Guards

Return complete enum options and the record resource. hasRates uses an existence query at the matching relationship depth; false is meaningful. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\BaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderStatusController
{
    public function create(Team $team): Response
    {
        return Inertia::render('work-order-statuses/Create', [
            'baseStatuses' => BaseStatus::options(),
            'team' => $team->toResource(),
        ]);
    }

    public function edit(Team $team, WorkOrderStatus $workOrderStatus): Response
    {
        return Inertia::render('work-order-statuses/Edit', [
            'baseStatuses' => BaseStatus::options(),
            'team' => $team->toResource(),
            'workOrderStatus' => $workOrderStatus->toResource(),
        ]);
    }

    public function show(Team $team, WorkOrderStatus $workOrderStatus): Response
    {
        return Inertia::render('work-order-statuses/Show', [
            'baseStatuses' => BaseStatus::options(),
            'team' => $team->toResource(),
            'workOrderStatus' => $workOrderStatus->toResource(),
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TransitTimeUnit;
use App\Enums\WeightUnit;
use App\Models\ServicePlan;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class ServicePlanController
{
    public function create(Team $team): Response
    {
        return Inertia::render('service-plans/Create', [
            'team' => $team->toResource(),
            'transitTimeUnits' => TransitTimeUnit::options(),
            'weightUnits' => WeightUnit::options(),
        ]);
    }

    public function edit(Team $team, ServicePlan $servicePlan): Response
    {
        return Inertia::render('service-plans/Edit', [
            'hasRates' => $servicePlan->planRules()->whereHas('rates')->exists(),
            'servicePlan' => $servicePlan->toResource(),
            'team' => $team->toResource(),
            'transitTimeUnits' => TransitTimeUnit::options(),
            'weightUnits' => WeightUnit::options(),
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Enums\RoundingMode;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class PlanRuleController
{
    public function create(Team $team, ServicePlan $servicePlan): Response
    {
        return Inertia::render('service-plans/plan-rules/Create', [
            'countryCodes' => CountryCode::options(),
            'currencyCodes' => CurrencyCode::options(),
            'roundingModes' => RoundingMode::options(),
            'servicePlan' => $servicePlan->toResource(),
            'team' => $team->toResource(),
        ]);
    }

    public function edit(Team $team, ServicePlan $servicePlan, PlanRule $planRule): Response
    {
        return Inertia::render('service-plans/plan-rules/Edit', [
            'countryCodes' => CountryCode::options(),
            'currencyCodes' => CurrencyCode::options(),
            'hasRates' => $planRule->rates()->exists(),
            'planRule' => $planRule->toResource(),
            'roundingModes' => RoundingMode::options(),
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

use App\Enums\AssignmentMode;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class TeamController
{
    public function show(Team $team): Response
    {
        return Inertia::render('team/Settings', [
            'assignmentModes' => AssignmentMode::options(),
            'team' => $team->toResource(),
        ]);
    }
}
```
