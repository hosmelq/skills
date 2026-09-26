# Controllers: Return Label and Public-ID Options

Only active parent records become label/value options. Values use sqid; this is a different schema from a resource collection. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\ServicePlan;
use App\Models\Team;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class CabinetController
{
    public function create(Team $team, Member $member): Response
    {
        return Inertia::render('members/cabinets/Create', [
            'member' => $member->toResource(),
            'servicePlans' => $this->servicePlanOptions($team),
            'team' => $team->toResource(),
        ]);
    }

    /**
     * @return Collection<int, array{label: string, value: string}>
     */
    private function servicePlanOptions(Team $team): Collection
    {
        return $team->servicePlans()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn (ServicePlan $servicePlan): array => [
                'label' => $servicePlan->name,
                'value' => $servicePlan->sqid,
            ]);
    }
}
```
