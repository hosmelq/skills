# Policies: Related Ownership on Update

Expose only update and viewAny. Update checks enrollment/member team consistency first, then membership; no state-transition predicate is supplied here.

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EnrollmentPolicy
{
    public function update(User $user, Enrollment $enrollment): bool|Response
    {
        if ($enrollment->team_id !== $enrollment->member->team_id) {
            return Response::denyAsNotFound();
        }

        return $user->belongsToTeam($enrollment->member->team);
    }

    public function viewAny(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }
}
```
