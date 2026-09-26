# Policies: Root Creation

Creating a root takes only the authenticated User and returns true; no team argument or membership check applies. View and update require team membership. A nonnullable User keeps guests denied by Gate.

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }

    public function view(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }
}
```
