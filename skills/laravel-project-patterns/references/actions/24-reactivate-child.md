# Actions: Reactivate Under an Active Parent

Verify the active parent within a transaction before reactivating the child. Missing or soft-deleted parents fail through firstOrFail.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Cabinets;

use App\Actions\ServicePlans\EnsureActiveServicePlan;
use App\Models\Cabinet;
use Illuminate\Support\Facades\DB;

class ReactivateCabinet
{
    public function __construct(private readonly EnsureActiveServicePlan $ensureActiveServicePlan)
    {
    }

    public function handle(Cabinet $cabinet): void
    {
        DB::transaction(function () use ($cabinet): void {
            $this->ensureActiveServicePlan->handle($cabinet->servicePlan()->firstOrFail());

            $cabinet->reactivate();
        });
    }
}
```
