<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\MembershipPermission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Middleware;
use Override;
use Spatie\Permission\Models\Permission;

final class HandleInertiaRequests extends Middleware
{
    #[Override]
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => $this->authProperties($request),
        ];
    }

    /**
     * @return array{clinic: null|JsonResource, permissions: list<MembershipPermission>, user: null|JsonResource}
     */
    private function authProperties(Request $request): array
    {
        $user = $request->user();

        return [
            'clinic' => $user?->currentClinic?->toResource(),
            'permissions' => $user?->currentClinic === null
                ? []
                : array_values($user->getAllPermissions()
                    ->ensure(Permission::class)
                    ->sortBy('name')
                    ->map(
                        fn (Permission $permission): MembershipPermission
                            => MembershipPermission::from($permission->name),
                    )
                    ->all()),
            'user' => $user?->toResource(),
        ];
    }
}
