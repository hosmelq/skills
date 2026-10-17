<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\MembershipPermission;
use App\Enums\MembershipRole;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class EnsureClinicRoles
{
    public function __construct(private readonly PermissionRegistrar $permissionRegistrar) {}

    public function handle(Clinic $clinic): void
    {
        DB::transaction(function () use ($clinic): void {
            $this->permissionRegistrar->forgetCachedPermissions();

            foreach (MembershipPermission::cases() as $permission) {
                Permission::findOrCreate($permission->value, 'web');
            }

            $this->permissionRegistrar->forgetCachedPermissions();

            foreach (MembershipRole::cases() as $membershipRole) {
                $role = Role::query()->firstOrCreate([
                    'clinic_id' => $clinic->id,
                    'guard_name' => 'web',
                    'name' => $membershipRole->value,
                ]);

                $role->syncPermissions($membershipRole->defaultPermissions());
            }
        });
    }
}
