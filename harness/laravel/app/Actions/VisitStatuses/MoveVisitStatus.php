<?php

declare(strict_types=1);

namespace App\Actions\VisitStatuses;

use function HosmelQ\AuditLog\audit_log;

use App\Enums\AuditLogEvent;
use App\Models\VisitStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class MoveVisitStatus
{
    public function handle(VisitStatus $visitStatus, null|VisitStatus $afterVisitStatus): void
    {
        DB::transaction(function () use ($afterVisitStatus, $visitStatus): void {
            if ($afterVisitStatus instanceof VisitStatus) {
                $visitStatus->moveAfter($afterVisitStatus);
            } else {
                $visitStatus->moveToStart();
            }

            /** @var array<string, null|bool|float|int|string> $before */
            $before = Arr::only($visitStatus->getPrevious(), 'sort_order');
            /** @var array<string, null|bool|float|int|string> $after */
            $after = Arr::only($visitStatus->getChanges(), 'sort_order');

            audit_log(AuditLogEvent::VisitStatusMoved)
                ->changes(before: $before, after: $after)
                ->target($visitStatus)
                ->tenant($visitStatus->clinic_id)
                ->record();
        });
    }
}
