<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Clinic;
use App\Models\Room;
use App\Models\User;

final class RoomPolicy
{
    public function create(User $user, Clinic $clinic): bool
    {
        return $user->belongsToClinic($clinic);
    }

    public function deactivate(User $user, Room $room): bool
    {
        return $user->belongsToClinic($room->clinic)
            && $room->isActive();
    }

    public function delete(User $user, Room $room): bool
    {
        return $user->belongsToClinic($room->clinic)
            && $room->isActive();
    }

    public function reactivate(User $user, Room $room): bool
    {
        return $user->belongsToClinic($room->clinic)
            && $room->isDeactivated();
    }

    public function update(User $user, Room $room): bool
    {
        return $user->belongsToClinic($room->clinic)
            && $room->isActive();
    }

    public function view(User $user, Room $room): bool
    {
        return $user->belongsToClinic($room->clinic);
    }

    public function viewAny(User $user, Clinic $clinic): bool
    {
        return $user->belongsToClinic($clinic);
    }
}
