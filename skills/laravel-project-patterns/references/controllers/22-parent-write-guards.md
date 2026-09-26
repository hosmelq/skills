# Controllers: Map Parent and Child Write Guards

Keep field-specific prohibited errors distinct from inactive-parent errors. The parent-unit guard and changed-child-currency guard remain action contracts. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\ServicePlans\Inputs\UpdateServicePlanInput;
use App\Actions\ServicePlans\UpdateServicePlan;
use App\Exceptions\CannotUpdateServicePlan;
use App\Http\Requests\UpdateServicePlanRequest;
use App\Models\ServicePlan;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class ServicePlanController
{
    public function update(
        UpdateServicePlanRequest $request,
        Team $team,
        ServicePlan $servicePlan,
        UpdateServicePlan $updateServicePlan,
    ): RedirectResponse {
        try {
            $updateServicePlan->handle(
                $servicePlan,
                UpdateServicePlanInput::from($request->validated()),
            );
        } catch (CannotUpdateServicePlan) {
            throw ValidationException::withMessages([
                'weight_unit' => __('validation.prohibited', ['attribute' => 'weight unit']),
            ]);
        }

        return to_route('teams.service-plans.show', [
            'service_plan' => $servicePlan,
            'team' => $team,
        ])->toast(__('service_plan.updated.title'));
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\ServicePlans\CreatePlanRule;
use App\Actions\ServicePlans\Inputs\CreatePlanRuleInput;
use App\Actions\ServicePlans\Inputs\UpdatePlanRuleInput;
use App\Actions\ServicePlans\UpdatePlanRule;
use App\Exceptions\CannotUpdatePlanRule;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Http\Requests\StorePlanRuleRequest;
use App\Http\Requests\UpdatePlanRuleRequest;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class PlanRuleController
{
    public function store(
        StorePlanRuleRequest $request,
        Team $team,
        ServicePlan $servicePlan,
        CreatePlanRule $createPlanRule
    ): RedirectResponse {
        try {
            $planRule = $createPlanRule->handle(
                $servicePlan,
                CreatePlanRuleInput::from($request->validated())
            );
        } catch (CannotUseDeactivatedServicePlan) {
            throw ValidationException::withMessages([
                'service_plan' => __('service_plan.validation.deactivated'),
            ]);
        }

        return to_route('teams.service-plans.plan-rules.show', [
            'plan_rule' => $planRule,
            'service_plan' => $servicePlan,
            'team' => $team,
        ])->toast(__('plan_rule.created.title'));
    }

    public function update(
        UpdatePlanRuleRequest $request,
        Team $team,
        ServicePlan $servicePlan,
        PlanRule $planRule,
        UpdatePlanRule $updatePlanRule
    ): RedirectResponse {
        try {
            $updatePlanRule->handle(
                $planRule,
                UpdatePlanRuleInput::from($request->validated())
            );
        } catch (CannotUpdatePlanRule) {
            throw ValidationException::withMessages([
                'currency_code' => __('validation.prohibited', ['attribute' => 'currency code']),
            ]);
        } catch (CannotUseDeactivatedServicePlan) {
            throw ValidationException::withMessages([
                'service_plan' => __('service_plan.validation.deactivated'),
            ]);
        }

        return to_route('teams.service-plans.plan-rules.show', [
            'plan_rule' => $planRule,
            'service_plan' => $servicePlan,
            'team' => $team,
        ])->toast(__('plan_rule.updated.title'));
    }
}
```
