# Controllers: Expose a Historical Selected Parent

Load the selected parent withTrashed and fail when missing. Apply the same relation contract to edit and show. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Cabinet;
use App\Models\Member;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class CabinetController
{
    public function edit(Team $team, Member $member, Cabinet $cabinet): Response
    {
        return Inertia::render('members/cabinets/Edit', [
            'cabinet' => $cabinet->toResource(),
            'member' => $member->toResource(),
            'servicePlan' => $cabinet->servicePlan()->withTrashed()->firstOrFail()->toResource(),
            'team' => $team->toResource(),
        ]);
    }

    public function show(Team $team, Member $member, Cabinet $cabinet): Response
    {
        return Inertia::render('members/cabinets/Show', [
            'cabinet' => $cabinet->toResource(),
            'member' => $member->toResource(),
            'servicePlan' => $cabinet->servicePlan()->withTrashed()->firstOrFail()->toResource(),
            'team' => $team->toResource(),
        ]);
    }
}
```
