<?php

declare(strict_types=1);

namespace App\Listeners;

use function HosmelQ\AuditLog\audit_log;

use App\Enums\AuditLogEvent;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Arr;

final class RecordVerifiedEmail
{
    public function handle(Verified $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $log = audit_log(AuditLogEvent::AuthEmailVerified);

        if ($event->user->wasChanged('email_verified_at')) {
            /** @var array<string, null|bool|float|int|string> $before */
            $before = Arr::only($event->user->getPrevious(), 'email_verified_at');
            /** @var array<string, null|bool|float|int|string> $after */
            $after = Arr::only($event->user->getChanges(), 'email_verified_at');

            $log->changes(before: $before, after: $after);
        }

        $log->actor($event->user)
            ->target($event->user)
            ->tenant($event->user->current_clinic_id ?? '')
            ->record();
    }
}
