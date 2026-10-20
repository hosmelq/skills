<?php

declare(strict_types=1);

namespace App\Actions\VisitStatuses;

use function HosmelQ\AuditLog\audit_log;

use App\Enums\AuditLogEvent;
use App\Exceptions\CannotSetInitialVisitStatus;
use App\Models\VisitStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class SetInitialVisitStatus
{
    public function handle(VisitStatus $visitStatus): VisitStatus
    {
        return DB::transaction(function () use ($visitStatus): VisitStatus {
            if (! $visitStatus->canBeInitial()) {
                throw CannotSetInitialVisitStatus::becauseItIsNotActiveCheckedIn();
            }

            VisitStatus::query()
                ->withTrashed()
                ->where('clinic_id', $visitStatus->clinic_id)
                ->where('is_initial', true)
                ->update(['is_initial' => false]);

            $visitStatus->update(['is_initial' => true]);

            /** @var array<string, null|bool|float|int|string> $before */
            $before = Arr::only($visitStatus->getPrevious(), 'is_initial');
            /** @var array<string, null|bool|float|int|string> $after */
            $after = Arr::only($visitStatus->getChanges(), 'is_initial');

            audit_log(AuditLogEvent::VisitStatusInitialSet)
                ->changes(before: $before, after: $after)
                ->target($visitStatus)
                ->tenant($visitStatus->clinic_id)
                ->record();

            return $visitStatus->refresh();
        });
    }
}
