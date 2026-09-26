# Policies: Related Ownership and State

Check the cabinet/member team IDs before membership or lifecycle. An internal mismatch returns 404; ordinary membership or state denial returns false. View has no lifecycle restriction, while write abilities preserve their active/deactivated checks.

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Cabinet;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CabinetPolicy
{
    public function create(User $user, Member $member): bool
    {
        return $user->belongsToTeam($member->team);
    }

    public function deactivate(User $user, Cabinet $cabinet): bool|Response
    {
        if ($cabinet->team_id !== $cabinet->member->team_id) {
            return Response::denyAsNotFound();
        }

        return $user->belongsToTeam($cabinet->member->team)
            && $cabinet->isActive();
    }

    public function delete(User $user, Cabinet $cabinet): bool|Response
    {
        if ($cabinet->team_id !== $cabinet->member->team_id) {
            return Response::denyAsNotFound();
        }

        return $user->belongsToTeam($cabinet->member->team)
            && $cabinet->isActive();
    }

    public function reactivate(User $user, Cabinet $cabinet): bool|Response
    {
        if ($cabinet->team_id !== $cabinet->member->team_id) {
            return Response::denyAsNotFound();
        }

        return $user->belongsToTeam($cabinet->member->team)
            && $cabinet->isDeactivated();
    }

    public function update(User $user, Cabinet $cabinet): bool|Response
    {
        if ($cabinet->team_id !== $cabinet->member->team_id) {
            return Response::denyAsNotFound();
        }

        return $user->belongsToTeam($cabinet->member->team)
            && $cabinet->isActive();
    }

    public function view(User $user, Cabinet $cabinet): bool|Response
    {
        if ($cabinet->team_id !== $cabinet->member->team_id) {
            return Response::denyAsNotFound();
        }

        return $user->belongsToTeam($cabinet->member->team);
    }

    public function viewAny(User $user, Member $member): bool
    {
        return $user->belongsToTeam($member->team);
    }
}
```
