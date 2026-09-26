# Actions: Dispatch on a Setting Transition

Update settings, then dispatch only when a feature flag actually changed to true. Omitted values, unchanged true and transitions to false do not dispatch.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Teams;

use App\Actions\Teams\Inputs\UpdateTeamInput;
use App\Jobs\ProvisionTeamCabinets;
use App\Models\Team;

class UpdateTeam
{
    public function handle(Team $team, UpdateTeamInput $input): Team
    {
        $team->update($input->transform());

        if ($team->wasChanged('cabinets_enabled') && $team->cabinets_enabled) {
            dispatch(new ProvisionTeamCabinets($team));
        }

        return $team;
    }
}
```
