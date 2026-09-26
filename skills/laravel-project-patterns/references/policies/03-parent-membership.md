# Policies: Parent Membership

Create and list take the member. Instance abilities follow address → member → team; membership is checked against that related team.

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Member;
use App\Models\MemberAddress;
use App\Models\User;

class MemberAddressPolicy
{
    public function create(User $user, Member $member): bool
    {
        return $user->belongsToTeam($member->team);
    }

    public function delete(User $user, MemberAddress $address): bool
    {
        return $user->belongsToTeam($address->member->team);
    }

    public function update(User $user, MemberAddress $address): bool
    {
        return $user->belongsToTeam($address->member->team);
    }

    public function view(User $user, MemberAddress $address): bool
    {
        return $user->belongsToTeam($address->member->team);
    }

    public function viewAny(User $user, Member $member): bool
    {
        return $user->belongsToTeam($member->team);
    }
}
```
