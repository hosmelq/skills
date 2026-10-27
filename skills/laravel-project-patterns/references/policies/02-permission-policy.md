# Policies: Permission Policy

Require a membership permission on top of tenant membership for settings abilities, while any user may create a tenant.

- Check permissions with `$user->can()` and the permission enum case; roles grant them through their default permissions.
- In tests, switch the user into the tenant before assigning roles or permissions, and assert the record is unchanged.
- Test denials through the endpoints in the controller's feature test, inside the `describe` block of the action.

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\MembershipPermission;
use App\Models\Clinic;
use App\Models\User;

final class ClinicPolicy
{
    public function create(): bool
    {
        return true;
    }

    public function update(User $user, Clinic $clinic): bool
    {
        return $user->belongsToClinic($clinic)
            && $user->can(MembershipPermission::SettingsManage);
    }

    public function view(User $user, Clinic $clinic): bool
    {
        return $user->belongsToClinic($clinic)
            && $user->can(MembershipPermission::SettingsView);
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Enums\MembershipPermission;
use App\Enums\MembershipRole;
use App\Models\Clinic;

describe('update', function (): void {
    it('requires settings.manage permission', function (MembershipRole $role): void {
        $clinic = Clinic::factory()->createOne(['name' => 'Original name']);
        $user = login(clinic: $clinic);

        $user->switchClinic($clinic);
        $user->assignRole($role);
        $user->givePermissionTo(MembershipPermission::SettingsView);

        $response = patch(route('clinics.update', [
            'clinic' => $clinic,
        ]), [
            'name' => 'Updated name',
        ]);

        $response->assertForbidden();

        expect($clinic->fresh())->name->toBe('Original name');
    })->with([
        'staff' => MembershipRole::Staff,
    ]);
});
```
