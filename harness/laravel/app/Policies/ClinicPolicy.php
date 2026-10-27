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
