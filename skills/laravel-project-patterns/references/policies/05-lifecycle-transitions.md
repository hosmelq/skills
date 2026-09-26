# Policies: Lifecycle Transitions

Only deactivate/reactivate depend on lifecycle state. Update and delete remain membership checks, including for deactivated records.

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ItemGroup;
use App\Models\Team;
use App\Models\User;

class ItemGroupPolicy
{
    public function create(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }

    public function deactivate(User $user, ItemGroup $itemGroup): bool
    {
        return $user->belongsToTeam($itemGroup->team)
            && $itemGroup->isActive();
    }

    public function delete(User $user, ItemGroup $itemGroup): bool
    {
        return $user->belongsToTeam($itemGroup->team);
    }

    public function reactivate(User $user, ItemGroup $itemGroup): bool
    {
        return $user->belongsToTeam($itemGroup->team)
            && $itemGroup->isDeactivated();
    }

    public function update(User $user, ItemGroup $itemGroup): bool
    {
        return $user->belongsToTeam($itemGroup->team);
    }

    public function view(User $user, ItemGroup $itemGroup): bool
    {
        return $user->belongsToTeam($itemGroup->team);
    }

    public function viewAny(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }
}
```
