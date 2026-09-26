# Controllers: Map Dynamic Range Errors

Translate the exception field and message from range actions; map unavailable ancestors separately. Preserve every ancestor in the redirect. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\ServicePlans\CreatePlanRate;
use App\Actions\ServicePlans\Inputs\CreatePlanRateInput;
use App\Actions\ServicePlans\Inputs\UpdatePlanRateInput;
use App\Actions\ServicePlans\UpdatePlanRate;
use App\Exceptions\CannotCreatePlanRate;
use App\Exceptions\CannotUpdatePlanRate;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Http\Requests\StorePlanRateRequest;
use App\Http\Requests\UpdatePlanRateRequest;
use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class PlanRateController
{
    public function store(
        StorePlanRateRequest $request,
        Team $team,
        ServicePlan $servicePlan,
        PlanRule $planRule,
        CreatePlanRate $createRate,
    ): RedirectResponse {
        try {
            $rate = $createRate->handle(
                $planRule,
                CreatePlanRateInput::from($request->validated()),
            );
        } catch (CannotCreatePlanRate $exception) {
            throw ValidationException::withMessages([
                $exception->field => __($exception->getMessage()),
            ]);
        } catch (CannotUseDeactivatedServicePlan) {
            throw ValidationException::withMessages([
                'service_plan' => __('service_plan.validation.deactivated'),
            ]);
        }

        return to_route('teams.service-plans.plan-rules.rates.show', [
            'plan_rule' => $planRule,
            'rate' => $rate,
            'service_plan' => $servicePlan,
            'team' => $team,
        ])->toast(__('plan_rate.created.title'));
    }

    public function update(
        UpdatePlanRateRequest $request,
        Team $team,
        ServicePlan $servicePlan,
        PlanRule $planRule,
        PlanRate $rate,
        UpdatePlanRate $updateRate,
    ): RedirectResponse {
        try {
            $updateRate->handle(
                $rate,
                UpdatePlanRateInput::from($request->validated()),
            );
        } catch (CannotUpdatePlanRate $exception) {
            throw ValidationException::withMessages([
                $exception->field => __($exception->getMessage()),
            ]);
        } catch (CannotUseDeactivatedServicePlan) {
            throw ValidationException::withMessages([
                'service_plan' => __('service_plan.validation.deactivated'),
            ]);
        }

        return to_route('teams.service-plans.plan-rules.rates.show', [
            'plan_rule' => $planRule,
            'rate' => $rate,
            'service_plan' => $servicePlan,
            'team' => $team,
        ])->toast(__('plan_rate.updated.title'));
    }
}
```
