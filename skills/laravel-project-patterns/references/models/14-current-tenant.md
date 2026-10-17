# Models: Current Tenant Selection

Let a user own tenants and belong to several, remember the active one in a nullable foreign key, fall back to the oldest membership, and switch only to tenants the user owns or belongs to, keeping the permission team in sync.

- Requires Spatie Permission with teams enabled.
- Membership is the pivot relation from the membership relations reference; membership and ownership checks accept `null`.
- Reading the default or the current tenant never persists or switches anything.
- Tests belong in the integration suite.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read int $id
 * @property-read null|int $current_clinic_id
 * @property-read Collection<int, Clinic> $clinics
 * @property-read null|Clinic $currentClinic
 * @property-read Collection<int, Clinic> $ownedClinics
 */
final class User extends Authenticatable
{
    use HasRoles;

    public function belongsToClinic(null|Clinic $clinic): bool
    {
        if (! $clinic instanceof Clinic) {
            return false;
        }

        if ($this->ownsClinic($clinic)) {
            return true;
        }

        return $this->clinics->contains($clinic);
    }

    /**
     * @return BelongsToMany<Clinic, $this, Membership, 'membership'>
     */
    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class)
            ->as('membership')
            ->using(Membership::class)
            ->withTimestamps();
    }

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function currentClinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'current_clinic_id');
    }

    public function defaultClinic(): null|Clinic
    {
        return $this->currentClinic ?? $this->clinics()
            ->oldest('clinic_user.created_at')
            ->oldest('clinic_user.id')
            ->first();
    }

    public function isCurrentClinic(Clinic $clinic): bool
    {
        if ($this->currentClinic === null) {
            return false;
        }

        return $this->currentClinic->id === $clinic->id;
    }

    /**
     * @return HasMany<Clinic, $this>
     */
    public function ownedClinics(): HasMany
    {
        return $this->hasMany(Clinic::class, 'owner_id');
    }

    public function ownsClinic(null|Clinic $clinic): bool
    {
        if (! $clinic instanceof Clinic) {
            return false;
        }

        return $this->id === $clinic->owner_id;
    }

    public function switchClinic(Clinic $clinic): bool
    {
        if (! $this->belongsToClinic($clinic)) {
            return false;
        }

        $this->update([
            'current_clinic_id' => $clinic->id,
        ]);

        $this->setRelation('currentClinic', $clinic);

        setPermissionsTeamId($clinic->id);

        $this->unsetRelation('permissions')->unsetRelation('roles');

        return true;
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Enums\MembershipPermission;
use App\Enums\MembershipRole;
use App\Models\Clinic;
use App\Models\User;
use Spatie\Permission\Models\Role;

it('checks is a user belongs to a clinic', function (): void {
    $clinic = Clinic::factory()->createOne();
    $user = User::factory()->createOne();

    expect($user->belongsToClinic($clinic))->toBeFalse();

    $clinic->users()->attach($user);

    $user->refresh();

    expect($user->belongsToClinic($clinic))->toBeTrue();
});

it('checks is a user owns a clinic', function (): void {
    $user = User::factory()->createOne();
    $userClinic = Clinic::factory()->for($user, 'owner')->createOne();
    $unrelatedClinic = Clinic::factory()->createOne();

    expect($user)->ownsClinic($unrelatedClinic)->toBeFalse()->ownsClinic($userClinic)->toBeTrue();
});

it('returns the current clinic as the default instead of the first membership', function (): void {
    $firstClinic = Clinic::factory()->createOne();
    $user = User::factory()->hasAttached($firstClinic)->withClinic()->createOne();
    $currentClinic = $user->currentClinic;
    $clinic = $user->defaultClinic();

    expect($clinic)
        ->is($currentClinic)
        ->toBeTrue()
        ->and($firstClinic)
        ->is($currentClinic)
        ->toBeFalse();
});

it('returns the first membership as the default without switching clinics', function (): void {
    $user = User::factory()->has(Clinic::factory()->count(2), 'clinics')->createOne();
    $firstClinic = $user->clinics->first();

    setPermissionsTeamId(123);

    $clinic = $user->defaultClinic();

    expect($clinic)
        ->is($firstClinic)
        ->toBeTrue()
        ->and($user->current_clinic_id)
        ->toBeNull()
        ->and(getPermissionsTeamId())
        ->toBe(123);

    assertDatabaseHas(User::class, [
        'current_clinic_id' => null,
        'id' => $user->id,
    ]);
});

it('cannot switch to an unrelated clinic', function (): void {
    $unrelatedClinic = Clinic::factory()->createOne();
    $user = User::factory()->createOne();

    expect($user)->switchClinic($unrelatedClinic)->toBeFalse()->currentClinic->toBeNull();
});

it('changes role and direct permissions when switching the active clinic', function (): void {
    $user = User::factory()->withClinic()->createOne();
    $originalClinic = $user->currentClinic;
    $newClinic = Clinic::factory()->recycle($user)->createOne();

    $user->clinics()->attach($newClinic);

    setPermissionsTeamId($originalClinic->id);

    $originalRole = Role::findByName(MembershipRole::Staff, 'web');

    $originalRole->syncPermissions(MembershipPermission::VisitsView);

    $user->assignRole($originalRole);
    $user->givePermissionTo(MembershipPermission::BoardingView);

    setPermissionsTeamId($newClinic->id);

    $newRole = Role::findByName(MembershipRole::Staff, 'web');

    $newRole->syncPermissions(MembershipPermission::VisitsManage);

    $user->assignRole($newRole);
    $user->givePermissionTo(MembershipPermission::BoardingManage);

    $user->switchClinic($originalClinic);
    $user->load(['permissions', 'roles']);

    expect($user)
        ->can(MembershipPermission::BoardingManage)->toBeFalse()
        ->can(MembershipPermission::BoardingView)->toBeTrue()
        ->can(MembershipPermission::VisitsManage)->toBeFalse()
        ->can(MembershipPermission::VisitsView)->toBeTrue();

    $switched = $user->switchClinic($newClinic);

    expect($switched)
        ->toBeTrue()
        ->and(getPermissionsTeamId())
        ->toBe($newClinic->id)
        ->and($user)
        ->can(MembershipPermission::BoardingManage)->toBeTrue()
        ->can(MembershipPermission::BoardingView)->toBeFalse()
        ->can(MembershipPermission::VisitsManage)->toBeTrue()
        ->can(MembershipPermission::VisitsView)->toBeFalse();

    assertDatabaseHas(User::class, [
        'current_clinic_id' => $newClinic->id,
        'id' => $user->id,
    ]);
});
```
