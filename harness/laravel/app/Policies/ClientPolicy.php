<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Client;
use App\Models\Clinic;
use App\Models\User;

final class ClientPolicy
{
    public function create(User $user, Clinic $clinic): bool
    {
        return $user->belongsToClinic($clinic);
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->belongsToClinic($client->clinic);
    }

    public function update(User $user, Client $client): bool
    {
        return $user->belongsToClinic($client->clinic);
    }

    public function view(User $user, Client $client): bool
    {
        return $user->belongsToClinic($client->clinic);
    }

    public function viewAny(User $user, Clinic $clinic): bool
    {
        return $user->belongsToClinic($clinic);
    }
}
