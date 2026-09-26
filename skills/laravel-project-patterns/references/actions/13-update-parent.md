# Actions: Update a Locked Parent

Lock and re-query a parent before updating Data fields. A supplied unit is rejected when dependent rates exist, even if it equals the stored value.

This example applies to a contract that forbids supplying the unit; a changed-value guard is a different contract. Return the locked instance, not the caller instance.

```php
<?php

declare(strict_types=1);

namespace App\Actions\ServicePlans;

use App\Actions\ServicePlans\Inputs\UpdateServicePlanInput;
use App\Exceptions\CannotUpdateServicePlan;
use App\Models\ServicePlan;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;

class UpdateServicePlan
{
    public function handle(ServicePlan $servicePlan, UpdateServicePlanInput $input): ServicePlan
    {
        return DB::transaction(function () use ($input, $servicePlan): ServicePlan {
            $lockedServicePlan = ServicePlan::query()
                ->whereKey($servicePlan)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $input->weightUnit instanceof Optional
                && $lockedServicePlan->planRules()->whereHas('rates')->exists()) {
                throw CannotUpdateServicePlan::becauseItHasRates();
            }

            $lockedServicePlan->update($input->transform());

            return $lockedServicePlan;
        });
    }
}
```
