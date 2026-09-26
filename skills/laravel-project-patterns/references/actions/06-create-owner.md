# Actions: Create and Select a Tenant

Transactional owner-scoped creation followed by initial-state setup, membership attachment and current-tenant selection, in that order.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Teams;

use App\Actions\Teams\Inputs\CreateTeamInput;
use App\Actions\WorkOrderStatuses\EnsureInitialWorkOrderStatus;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTeam
{
    public function __construct(
        private readonly EnsureInitialWorkOrderStatus $ensureInitialWorkOrderStatus,
    ) {
    }

    public function handle(User $user, CreateTeamInput $input): Team
    {
        return DB::transaction(function () use ($user, $input): Team {
            $team = $user->ownedTeams()->create($input->transform());

            $this->ensureInitialWorkOrderStatus->handle($team);

            $team->users()->attach($user);

            $user->switchTeam($team);

            return $team;
        });
    }
}
```
