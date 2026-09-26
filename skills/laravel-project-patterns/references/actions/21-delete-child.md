# Actions: Delete Under an Active Ancestor

Child and grandchild deletion verify an active parent through the exact relation path inside a transaction. The two paths preserve different ancestor traversal.

```php
<?php

declare(strict_types=1);

namespace App\Actions\ServicePlans;

use App\Models\PlanRule;
use Illuminate\Support\Facades\DB;

class DeletePlanRule
{
    public function __construct(private readonly EnsureActiveServicePlan $ensureActiveServicePlan)
    {
    }

    public function handle(PlanRule $planRule): void
    {
        DB::transaction(function () use ($planRule): void {
            $this->ensureActiveServicePlan->handle($planRule->servicePlan()->firstOrFail());

            $planRule->delete();
        });
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\ServicePlans;

use App\Models\PlanRate;
use Illuminate\Support\Facades\DB;

class DeletePlanRate
{
    public function __construct(private readonly EnsureActiveServicePlan $ensureActiveServicePlan)
    {
    }

    public function handle(PlanRate $rate): void
    {
        DB::transaction(function () use ($rate): void {
            $this->ensureActiveServicePlan->handle(
                $rate->planRule()->firstOrFail()->servicePlan()->firstOrFail()
            );

            $rate->delete();
        });
    }
}
```
