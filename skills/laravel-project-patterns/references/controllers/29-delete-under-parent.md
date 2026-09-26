# Controllers: Delete Under an Active Ancestor

Translate unavailable-ancestor errors to the parent field. A child deletion can return to parent show or the nested list; preserve the inspected route. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\ServicePlans\DeletePlanRule;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class PlanRuleController
{
    public function destroy(
        Team $team,
        ServicePlan $servicePlan,
        PlanRule $planRule,
        DeletePlanRule $deletePlanRule
    ): RedirectResponse {
        try {
            $deletePlanRule->handle($planRule);
        } catch (CannotUseDeactivatedServicePlan) {
            throw ValidationException::withMessages([
                'service_plan' => __('service_plan.validation.deactivated'),
            ]);
        }

        return to_route('teams.service-plans.show', [
            'service_plan' => $servicePlan,
            'team' => $team,
        ])->toast(__('plan_rule.deleted.title'));
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\ServicePlans\DeletePlanRate;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class PlanRateController
{
    public function destroy(
        Team $team,
        ServicePlan $servicePlan,
        PlanRule $planRule,
        PlanRate $rate,
        DeletePlanRate $deleteRate,
    ): RedirectResponse {
        try {
            $deleteRate->handle($rate);
        } catch (CannotUseDeactivatedServicePlan) {
            throw ValidationException::withMessages([
                'service_plan' => __('service_plan.validation.deactivated'),
            ]);
        }

        return to_route('teams.service-plans.plan-rules.rates.index', [
            'plan_rule' => $planRule,
            'service_plan' => $servicePlan,
            'team' => $team,
        ])->toast(__('plan_rate.deleted.title'));
    }
}
```
