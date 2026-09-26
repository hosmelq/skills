# Actions: Deactivate a Locked Parent

Lock and re-query a parent, reject active dependent records, then invoke its deactivation method inside the transaction.

```php
<?php

declare(strict_types=1);

namespace App\Actions\ServicePlans;

use App\Exceptions\CannotDeactivateServicePlan;
use App\Models\ServicePlan;
use Illuminate\Support\Facades\DB;

class DeactivateServicePlan
{
    public function handle(ServicePlan $servicePlan): void
    {
        DB::transaction(function () use ($servicePlan): void {
            $lockedServicePlan = ServicePlan::query()
                ->whereKey($servicePlan)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedServicePlan->cabinets()->active()->exists()) {
                throw CannotDeactivateServicePlan::becauseItHasActiveCabinets();
            }

            $lockedServicePlan->deactivate();
        });
    }
}
```
