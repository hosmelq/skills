<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\VisitStatus;

final class VisitStatusPolicy
{
    public function update(User $user, VisitStatus $visitStatus): bool
    {
        return $user->belongsToClinic($visitStatus->clinic);
    }
}
