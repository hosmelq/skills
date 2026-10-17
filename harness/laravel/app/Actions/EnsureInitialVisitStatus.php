<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\VisitStage;
use App\Models\Clinic;
use App\Models\VisitStatus;

final class EnsureInitialVisitStatus
{
    public function handle(Clinic $clinic): void
    {
        VisitStatus::query()->firstOrCreate(
            ['clinic_id' => $clinic->id, 'is_initial' => true],
            ['name' => 'Checked in', 'stage' => VisitStage::CheckedIn],
        );
    }
}
