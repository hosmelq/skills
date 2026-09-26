# Actions: Guard a Changed Child Field

Within the active-parent transaction, reject a supplied changed currency only when rates exist; omission and the same value remain valid. Persist the child and return void.

```php
<?php

declare(strict_types=1);

namespace App\Actions\ServicePlans;

use App\Actions\ServicePlans\Inputs\UpdatePlanRuleInput;
use App\Exceptions\CannotUpdatePlanRule;
use App\Models\PlanRule;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;

class UpdatePlanRule
{
    public function __construct(private readonly EnsureActiveServicePlan $ensureActiveServicePlan)
    {
    }

    public function handle(PlanRule $planRule, UpdatePlanRuleInput $input): void
    {
        DB::transaction(function () use ($input, $planRule): void {
            $this->ensureActiveServicePlan->handle($planRule->servicePlan()->firstOrFail());

            if (! $input->currencyCode instanceof Optional
                && $input->currencyCode !== $planRule->currency_code
                && $planRule->rates()->exists()) {
                throw CannotUpdatePlanRule::becauseItHasRates();
            }

            $planRule->update($input->transform());
        });
    }
}
```
