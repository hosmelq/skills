<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;

final class AuthenticatedUserController
{
    public function show(#[CurrentUser] User $user): UserResource
    {
        return UserResource::make($user);
    }
}
