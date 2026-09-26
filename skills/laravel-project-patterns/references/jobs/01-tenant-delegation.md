# Jobs: After-Commit Tenant Delegation

Keep the model in the constructor and inject the Action into `handle()`. Return before querying when the feature is disabled; query the team relation for approved records and traverse with `eachById()`. The Action owns each record’s writes and guards; the job supplies no overlap lock or atomic batch.

`ShouldQueueAfterCommit` requests dispatch after open transactions commit. `Queueable` serializes an Eloquent model identifier and restores the model for execution; loaded relationships may also be restored without their earlier constraints. Reapply selection inside the handler. Calling `handle()` directly does not test queue timing or model restoration.

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Cabinets\ProvisionMemberCabinets;
use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Team;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;

class ProvisionTeamCabinets implements ShouldQueueAfterCommit
{
    use Queueable;

    public function __construct(public Team $team)
    {
    }

    public function handle(ProvisionMemberCabinets $provisionMemberCabinets): void
    {
        if (! $this->team->cabinets_enabled) {
            return;
        }

        $this->team->enrollments()
            ->where('status', EnrollmentStatus::Approved)
            ->eachById(function (Enrollment $enrollment) use ($provisionMemberCabinets): void {
                $provisionMemberCabinets->handle($enrollment);
            });
    }
}
```

[Laravel queues](https://laravel.com/docs/13.x/queues) documents serialization and injection. Use the separate [job test checklist](../tests/jobs/00-job-test-order.md) for interface and selection assertions.
