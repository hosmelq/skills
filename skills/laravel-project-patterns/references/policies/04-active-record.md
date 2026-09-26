# Policies: Active Record Writes

Update, delete and deactivate require an active record; reactivate requires a deactivated record. Reads require membership only. ServicePlanPolicy uses the same complete ability set and predicates.

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Facility;
use App\Models\Team;
use App\Models\User;

class FacilityPolicy
{
    public function create(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }

    public function deactivate(User $user, Facility $facility): bool
    {
        return $user->belongsToTeam($facility->team)
            && $facility->isActive();
    }

    public function delete(User $user, Facility $facility): bool
    {
        return $user->belongsToTeam($facility->team)
            && $facility->isActive();
    }

    public function reactivate(User $user, Facility $facility): bool
    {
        return $user->belongsToTeam($facility->team)
            && $facility->isDeactivated();
    }

    public function update(User $user, Facility $facility): bool
    {
        return $user->belongsToTeam($facility->team)
            && $facility->isActive();
    }

    public function view(User $user, Facility $facility): bool
    {
        return $user->belongsToTeam($facility->team);
    }

    public function viewAny(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }
}
```
