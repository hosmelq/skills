# Actions: Accept Invitation

Add a user to the inviting tenant with the invited role: attach the membership without duplicates, mark the invitation accepted, switch the user into the tenant and assign the role, rolling everything back if the role is missing.

- Unset the loaded memberships before switching, so the membership check sees the new tenant.
- Record an `AuditLogEvent` case, added alphabetically, inside the transaction, targeting the record and its tenant.

```php
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
            $invitation->clinic->users()->syncWithoutDetaching([$user->id]);

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
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseMissing;

use App\Actions\Invitations\AcceptInvitation;
use App\Enums\AuditLogEvent;
use App\Models\Invitation;
use App\Models\User;
use HosmelQ\AuditLog\Data\AuditLogData;
use HosmelQ\AuditLog\Facades\AuditLog;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Role;

it('accepts an invitation for the user and switches to its clinic', function (): void {
    AuditLog::fake();

    $user = User::factory()->createOne();
    $invitation = Invitation::factory()->createOne(['email' => $user->email]);

    resolve(AcceptInvitation::class)->handle($user, $invitation);

    $user->refresh();
    $invitation->refresh();

    expect($user)
        ->belongsToClinic($invitation->clinic)->toBeTrue()
        ->current_clinic_id->toBe($invitation->clinic_id)
        ->and($invitation->accepted_at)->toEqual(now());

    AuditLog::assertRecorded(
        fn (AuditLogData $log): bool => $log->event === AuditLogEvent::InvitationAccepted->value
            && $log->changes?->before['accepted_at'] === null
            && $log->changes?->after['accepted_at'] !== null
            && $log->tenantId === (string) $invitation->clinic_id
            && $log->targets[0]->id === (string) $invitation->id,
    );
});

it('rolls back acceptance when the invited role is missing', function (): void {
    $user = User::factory()->withClinic()->createOne();
    $otherClinic = $user->currentClinic;
    $invitation = Invitation::factory()->createOne(['email' => $user->email]);

    Role::query()
        ->where('name', $invitation->role->value)
        ->where('clinic_id', $invitation->clinic_id)
        ->sole()
        ->delete();

    $user->switchClinic($otherClinic);

    expect(function () use ($invitation, $user): void {
        resolve(AcceptInvitation::class)->handle($user, $invitation);
    })->toThrow(RoleDoesNotExist::class);

    assertDatabaseMissing('clinic_user', [
        'clinic_id' => $invitation->clinic_id,
        'user_id' => $user->id,
    ]);
    assertDatabaseCount('model_has_roles', 0);

    expect($user->fresh())
        ->current_clinic_id
        ->toBe($otherClinic->id)
        ->and($invitation->fresh())
        ->accepted_at
        ->toBeNull();
});
```
