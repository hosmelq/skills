# Listeners: Audit Listener

Record framework authentication events in the audit log with discovered listeners, using the user as actor and target and their current tenant, with the changed attribute when the event changed one.

- Type-hint the event in `handle()` so Laravel discovers the listener; ignore users of other models.
- Test through the endpoints that fire the events.

```php
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
```

```php
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
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;

use App\Enums\AuditLogEvent;
use App\Models\OneTimePassword;
use App\Models\User;
use HosmelQ\AuditLog\Data\AuditLogData;
use HosmelQ\AuditLog\Facades\AuditLog;

it('records auth.login and auth.email_verified', function (): void {
    AuditLog::fake();

    OneTimePassword::factory()->createOne([
        'code' => '111111',
        'email' => 'john@gmail.com',
    ]);
    $user = User::factory()
        ->unverified()
        ->withClinic()
        ->createOne([
            'email' => 'john@gmail.com',
        ]);
    $response = postJson(route('api.auth.email.login'), [
        'code' => '111111',
        'email' => 'john@gmail.com',
    ]);

    $response->assertOk();

    AuditLog::assertRecorded(
        fn (AuditLogData $log): bool => $log->event === AuditLogEvent::AuthLogin->value
            && $log->tenantId === (string) $user->current_clinic_id
            && $log->targets[0]->id === (string) $user->id
            && $log->actor->id === (string) $user->id,
    );

    AuditLog::assertRecorded(
        fn (AuditLogData $log): bool => $log->event === AuditLogEvent::AuthEmailVerified->value
            && $log->tenantId === (string) $user->current_clinic_id
            && $log->targets[0]->id === (string) $user->id
            && $log->changes?->before['email_verified_at'] === null
            && $log->changes?->after['email_verified_at'] === now()->toDateTimeString(),
    );
});
```
