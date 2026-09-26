# Actions: Lock and Resolve an Active Parent

Re-query a parent by key with lockForUpdate, reject inactive state and return the locked instance. Call inside the caller transaction before dependent reads or writes.

The helper does not open a transaction. Its callers own the lock lifetime; read-then-write guards alone do not establish concurrency safety.

```php
<?php

declare(strict_types=1);

namespace App\Actions\ServicePlans;

use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\ServicePlan;

class EnsureActiveServicePlan
{
    public function handle(ServicePlan $servicePlan): ServicePlan
    {
        $lockedServicePlan = ServicePlan::query()
            ->whereKey($servicePlan)
            ->lockForUpdate()
            ->firstOrFail();

        if ($lockedServicePlan->deactivated_at !== null) {
            throw CannotUseDeactivatedServicePlan::becauseItIsDeactivated();
        }

        return $lockedServicePlan;
    }
}
```
