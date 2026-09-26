# Actions: Delete a Locked Referenced Parent

Lock the parent, then reject historical references across several relations, including trashed rows, before deletion. All checks and deletion share the transaction.

```php
<?php

declare(strict_types=1);

namespace App\Actions\ServicePlans;

use App\Exceptions\CannotDeleteServicePlanInUse;
use App\Models\ServicePlan;
use Illuminate\Support\Facades\DB;

class DeleteServicePlan
{
    public function handle(ServicePlan $servicePlan): void
    {
        DB::transaction(function () use ($servicePlan): void {
            $lockedServicePlan = ServicePlan::query()
                ->whereKey($servicePlan)
                ->lockForUpdate()
                ->firstOrFail();

            throw_if(
                $lockedServicePlan->cabinets()->withTrashed()->exists()
                || $lockedServicePlan->workOrders()->withTrashed()->exists()
                || $lockedServicePlan->planRules()->withTrashed()->exists(),
                CannotDeleteServicePlanInUse::class
            );

            $lockedServicePlan->delete();
        });
    }
}
```
