# Actions: Create Under an Active Parent

Create a child through the locked active parent relation inside a transaction. The helper returns the re-queried parent; use that instance for creation.

```php
<?php

declare(strict_types=1);

namespace App\Actions\ServicePlans;

use App\Actions\ServicePlans\Inputs\CreatePlanRuleInput;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use Illuminate\Support\Facades\DB;

class CreatePlanRule
{
    public function __construct(private readonly EnsureActiveServicePlan $ensureActiveServicePlan)
    {
    }

    public function handle(ServicePlan $servicePlan, CreatePlanRuleInput $input): PlanRule
    {
        return DB::transaction(function () use ($input, $servicePlan): PlanRule {
            $activeServicePlan = $this->ensureActiveServicePlan->handle($servicePlan);

            return $activeServicePlan->planRules()->create($input->transform());
        });
    }
}
```
