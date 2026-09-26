# Policies: Team Membership

Create and list take the team; instance abilities follow the record’s team. The same complete ability set applies to WorkOrderPolicy by substituting its model type. No lifecycle or final-state predicate is implied.

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Member;
use App\Models\Team;
use App\Models\User;

class MemberPolicy
{
    public function create(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }

    public function delete(User $user, Member $member): bool
    {
        return $user->belongsToTeam($member->team);
    }

    public function update(User $user, Member $member): bool
    {
        return $user->belongsToTeam($member->team);
    }

    public function view(User $user, Member $member): bool
    {
        return $user->belongsToTeam($member->team);
    }

    public function viewAny(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }
}
```
