<?php

declare(strict_types=1);

namespace App\Listeners;

use function HosmelQ\AuditLog\audit_log;

use App\Enums\AuditLogEvent;
use App\Models\User;
use Illuminate\Auth\Events\Login;

final class RecordLogin
{
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        audit_log(AuditLogEvent::AuthLogin)
            ->actor($event->user)
            ->target($event->user)
            ->tenant($event->user->current_clinic_id ?? '')
            ->record();
    }
}
