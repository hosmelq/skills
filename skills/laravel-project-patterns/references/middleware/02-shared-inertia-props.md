# Middleware: Shared Inertia Props

Share the signed-in user, their current tenant and their permissions in that tenant with every Inertia page, so the frontend can render navigation and hide actions the user cannot take.

- Append it to the `web` group after the tenant permission context.
- Share resources, never models, and permissions as a sorted list of permission enum cases, empty without a current tenant.
- Call `JsonResource::withoutWrapping()` in a service provider so resources share their fields without a `data` key.

```php
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
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\MembershipPermission;
use App\Enums\MembershipRole;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetClinicPermissionContext;
use App\Models\Clinic;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Testing\AssertableInertia;

it('shares null authentication data for guests', function (): void {
    Route::middleware(HandleInertiaRequests::class)
        ->get('/_test', fn () => Inertia::render('auth/Login'));

    $response = get('/_test');

    $response->assertOk()
        ->assertInertia(function (AssertableInertia $page): void {
            $page
                ->where('auth.clinic', null)
                ->where('auth.permissions', [])
                ->where('auth.user', null);
        });
});

it('shares only the sorted permissions for the current clinic role', function (): void {
    Route::middleware([SetClinicPermissionContext::class, HandleInertiaRequests::class])
        ->get('/_test', fn () => Inertia::render('auth/Login'));

    $clinic = Clinic::factory()->createOne();
    $otherClinic = Clinic::factory()->createOne();
    $user = login(clinic: $clinic);

    $user->clinics()->attach($otherClinic);
    $user->switchClinic($otherClinic);
    $user->assignRole(MembershipRole::Owner);
    $user->load(['permissions', 'roles']);
    $user->switchClinic($clinic);
    $user->assignRole(MembershipRole::Staff);

    $response = get('/_test');

    $response->assertOk()
        ->assertInertia(function (AssertableInertia $page) use ($clinic, $user): void {
            $page
                ->where('auth.clinic.id', $clinic->sqid)
                ->where('auth.permissions', [
                    MembershipPermission::VisitsView->value,
                ])
                ->where('auth.user.id', $user->sqid);
        });
});
```
