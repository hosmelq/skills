<?php

declare(strict_types=1);

namespace App\Actions\Invitations;

use function HosmelQ\AuditLog\audit_log;

use App\Enums\AuditLogEvent;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class AcceptInvitation
{
    public function handle(User $user, Invitation $invitation): void
    {
        DB::transaction(function () use ($invitation, $user): void {
            $invitation
                ->clinic
                ->users()
                ->syncWithoutDetaching([$user->id]);

            $invitation->update([
                'accepted_at' => now(),
            ]);

            $user->unsetRelation('clinics');

            $user->switchClinic($invitation->clinic);
            $user->assignRole($invitation->role);

            /** @var array<string, null|bool|float|int|string> $before */
            $before = Arr::only($invitation->getPrevious(), ['accepted_at']);
            /** @var array<string, null|bool|float|int|string> $after */
            $after = Arr::only($invitation->getChanges(), ['accepted_at']);

            audit_log(AuditLogEvent::InvitationAccepted)
                ->changes(before: $before, after: $after)
                ->target($invitation)
                ->tenant($invitation->clinic_id)
                ->record();
        });
    }
}
