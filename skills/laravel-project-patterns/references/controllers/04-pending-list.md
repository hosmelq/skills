# Controllers: List Pending Reviews

Filter the requested state and related owner tenant, eager-load the displayed relations, then order by request time and paginate. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Http\Resources\EnrollmentResource;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class EnrollmentController
{
    public function index(Team $team): Response
    {
        $enrollments = $team->enrollments()
            ->where('status', EnrollmentStatus::Pending)
            ->whereRelation('member', 'team_id', $team->id)
            ->with(['member', 'requestedByUser'])
            ->latest('requested_at')
            ->paginate();

        return Inertia::render('enrollments/Index', [
            'enrollments' => EnrollmentResource::collection($enrollments),
            'team' => $team->toResource(),
        ]);
    }
}
```
