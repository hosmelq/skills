# Controllers: Render Basic Record Pages

Return explicit resource props for create, edit and show. A nullable default relation remains null. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class MemberController
{
    public function create(Team $team): Response
    {
        return Inertia::render('members/Create', [
            'team' => $team->toResource(),
        ]);
    }

    public function edit(Team $team, Member $member): Response
    {
        return Inertia::render('members/Edit', [
            'member' => $member->toResource(),
            'team' => $team->toResource(),
        ]);
    }

    public function show(Team $team, Member $member): Response
    {
        return Inertia::render('members/Show', [
            'defaultAddress' => $member->defaultAddress?->toResource(),
            'member' => $member->toResource(),
            'team' => $team->toResource(),
        ]);
    }
}
```
