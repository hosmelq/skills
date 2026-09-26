# Actions: Dispatch After Creation

Relation creation followed by conditional job dispatch when a feature is enabled, then returning the created model. This action does not start a transaction.

```php
<?php

declare(strict_types=1);

namespace App\Actions\ServicePlans;

use App\Actions\ServicePlans\Inputs\CreateServicePlanInput;
use App\Jobs\ProvisionTeamCabinets;
use App\Models\ServicePlan;
use App\Models\Team;

class CreateServicePlan
{
    public function handle(Team $team, CreateServicePlanInput $input): ServicePlan
    {
        $servicePlan = $team->servicePlans()->create($input->transform());

        if ($team->cabinets_enabled) {
            dispatch(new ProvisionTeamCabinets($team));
        }

        return $servicePlan;
    }
}
```
