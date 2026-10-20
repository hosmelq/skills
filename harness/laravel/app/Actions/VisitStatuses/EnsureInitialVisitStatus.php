<?php

declare(strict_types=1);

namespace App\Actions\VisitStatuses;

use App\Enums\VisitStage;
use App\Models\Clinic;
use App\Models\VisitStatus;
use Illuminate\Support\Facades\DB;

final readonly class EnsureInitialVisitStatus
{
    public function __construct(private SetInitialVisitStatus $setInitialVisitStatus) {}

    public function handle(Clinic $clinic): VisitStatus
    {
        return DB::transaction(function () use ($clinic): VisitStatus {
            $initialVisitStatus = $clinic->visitStatuses()
                ->where('is_initial', true)
                ->where('stage', VisitStage::CheckedIn)
                ->whereNull('deactivated_at')
                ->first();

            if ($initialVisitStatus instanceof VisitStatus) {
                return $initialVisitStatus;
            }

            $checkedInVisitStatus = $clinic->visitStatuses()
                ->where('stage', VisitStage::CheckedIn)
                ->whereNull('deactivated_at')
                ->ordered()
                ->first();

            if ($checkedInVisitStatus instanceof VisitStatus) {
                return $this->setInitialVisitStatus->handle($checkedInVisitStatus);
            }

            return $clinic->visitStatuses()->create([
                'is_initial' => true,
                'name' => trans()->string('visit_status.defaults.initial_name'),
                'stage' => VisitStage::CheckedIn,
            ]);
        });
    }
}
