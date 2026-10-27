<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Clinics\EnsureClinicRoles;
use App\Actions\VisitStatuses\EnsureInitialVisitStatus;
use App\Models\Clinic;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;

final class EnsureClinicDefaults implements ShouldQueueAfterCommit
{
    use Queueable;

    public function handle(
        EnsureClinicRoles $ensureClinicRoles,
        EnsureInitialVisitStatus $ensureInitialVisitStatus,
    ): void {
        Clinic::query()->eachById(
            function (Clinic $clinic) use ($ensureClinicRoles, $ensureInitialVisitStatus): void {
                $ensureClinicRoles->handle($clinic);
                $ensureInitialVisitStatus->handle($clinic);
            },
        );
    }
}
