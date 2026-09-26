# Policies: Ordered Ancestor Context

Class abilities receive team, plan and rule in that order. Membership follows the rule’s plan; create checks the separately supplied plan’s state. These predicates do not prove the supplied parents match: inspect scoped binding or an explicit consistency guard. Instance writes follow rate → rule → plan.

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\User;

class PlanRatePolicy
{
    public function create(
        User $user,
        Team $team,
        ServicePlan $servicePlan,
        PlanRule $planRule
    ): bool {
        return $user->belongsToTeam($planRule->servicePlan->team)
            && $servicePlan->deactivated_at === null;
    }

    public function delete(User $user, PlanRate $rate): bool
    {
        return $user->belongsToTeam($rate->planRule->servicePlan->team)
            && $rate->planRule->servicePlan->deactivated_at === null;
    }

    public function update(User $user, PlanRate $rate): bool
    {
        return $user->belongsToTeam($rate->planRule->servicePlan->team)
            && $rate->planRule->servicePlan->deactivated_at === null;
    }

    public function view(User $user, PlanRate $rate): bool
    {
        return $user->belongsToTeam($rate->planRule->servicePlan->team);
    }

    public function viewAny(
        User $user,
        Team $team,
        ServicePlan $servicePlan,
        PlanRule $planRule
    ): bool {
        return $user->belongsToTeam($planRule->servicePlan->team);
    }
}
```
