<?php

declare(strict_types=1);

namespace App\Actions\Rooms;

use function HosmelQ\AuditLog\audit_log;

use App\Enums\AuditLogEvent;
use App\Models\Room;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class ReactivateRoom
{
    public function handle(Room $room): void
    {
        DB::transaction(function () use ($room): void {
            $room->reactivate();

            /** @var array<string, null|bool|float|int|string> $before */
            $before = Arr::only($room->getPrevious(), 'deactivated_at');
            /** @var array<string, null|bool|float|int|string> $after */
            $after = Arr::only($room->getChanges(), 'deactivated_at');

            audit_log(AuditLogEvent::RoomReactivated)
                ->changes(before: $before, after: $after)
                ->target($room)
                ->tenant($room->clinic_id)
                ->record();
        });
    }
}
