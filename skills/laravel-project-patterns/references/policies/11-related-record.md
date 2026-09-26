# Policies: Related Ownership on a Child

Create takes the work order. Instance abilities compare the line and parent team IDs, returning 404 on mismatch, then check the line’s team membership. There is no viewAny or final-state check in this shape.

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use Illuminate\Auth\Access\Response;

class WorkOrderLinePolicy
{
    public function create(User $user, WorkOrder $workOrder): bool
    {
        return $user->belongsToTeam($workOrder->team);
    }

    public function delete(User $user, WorkOrderLine $line): bool|Response
    {
        if ($line->team_id !== $line->workOrder->team_id) {
            return Response::denyAsNotFound();
        }

        return $user->belongsToTeam($line->team);
    }

    public function update(User $user, WorkOrderLine $line): bool|Response
    {
        if ($line->team_id !== $line->workOrder->team_id) {
            return Response::denyAsNotFound();
        }

        return $user->belongsToTeam($line->team);
    }

    public function view(User $user, WorkOrderLine $line): bool|Response
    {
        if ($line->team_id !== $line->workOrder->team_id) {
            return Response::denyAsNotFound();
        }

        return $user->belongsToTeam($line->team);
    }
}
```
