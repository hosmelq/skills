# Policies: Explicit Deactivation Timestamp

Reactivation checks `deactivated_at !== null` because the status model has no `isDeactivated()` helper; its `isActive()`, used by deactivate, also excludes soft-deleted records. Update and delete require membership only; any delete or transition guard is outside this policy.

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use App\Models\WorkOrderStatus;

class WorkOrderStatusPolicy
{
    public function create(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }

    public function deactivate(User $user, WorkOrderStatus $workOrderStatus): bool
    {
        return $user->belongsToTeam($workOrderStatus->team)
            && $workOrderStatus->isActive();
    }

    public function delete(User $user, WorkOrderStatus $workOrderStatus): bool
    {
        return $user->belongsToTeam($workOrderStatus->team);
    }

    public function reactivate(User $user, WorkOrderStatus $workOrderStatus): bool
    {
        return $user->belongsToTeam($workOrderStatus->team)
            && $workOrderStatus->deactivated_at !== null;
    }

    public function update(User $user, WorkOrderStatus $workOrderStatus): bool
    {
        return $user->belongsToTeam($workOrderStatus->team);
    }

    public function view(User $user, WorkOrderStatus $workOrderStatus): bool
    {
        return $user->belongsToTeam($workOrderStatus->team);
    }

    public function viewAny(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }
}
```
