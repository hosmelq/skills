# Policies: Active Parent Writes

Create, update and delete require the parent’s deactivated_at to be null. Reads allow an inactive parent. Instance checks follow rule → plan → team.

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\User;

class PlanRulePolicy
{
    public function create(User $user, ServicePlan $servicePlan): bool
    {
        return $user->belongsToTeam($servicePlan->team)
            && $servicePlan->deactivated_at === null;
    }

    public function delete(User $user, PlanRule $planRule): bool
    {
        return $user->belongsToTeam($planRule->servicePlan->team)
            && $planRule->servicePlan->deactivated_at === null;
    }

    public function update(User $user, PlanRule $planRule): bool
    {
        return $user->belongsToTeam($planRule->servicePlan->team)
            && $planRule->servicePlan->deactivated_at === null;
    }

    public function view(User $user, PlanRule $planRule): bool
    {
        return $user->belongsToTeam($planRule->servicePlan->team);
    }

    public function viewAny(User $user, ServicePlan $servicePlan): bool
    {
        return $user->belongsToTeam($servicePlan->team);
    }
}
```
