# Middleware: Tenant Permission Context

Scope permission checks of each web request to the user's current tenant: switch users without one into their default tenant, set the permission team for the request, forbid routes of any other tenant and clear the team afterwards.

- Append it to the `web` group in `bootstrap/app.php`.
- Clear the team in `finally`, so it never leaks into the next request or job.

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Clinic;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetClinicPermissionContext
{
    /**
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = $request->user();

            if (
                $user !== null
                && $user->current_clinic_id === null
                && ($defaultClinic = $user->defaultClinic()) !== null
            ) {
                $user->switchClinic($defaultClinic);
            }

            $activeClinic = $user?->currentClinic;

            setPermissionsTeamId($activeClinic?->id);

            $clinic = $request->route('clinic');

            if ($user !== null && $clinic instanceof Clinic) {
                abort_unless($user->isCurrentClinic($clinic), 403);
            }

            return $next($request);
        } finally {
            setPermissionsTeamId(null);
        }
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Support\Facades\Route;

it('uses the active clinic and clears the team after the request', function (): void {
    $user = User::factory()->withClinic()->createOne();

    login($user);

    Route::middleware('web')
        ->get('/_test/permission-context', fn (): array => ['team_id' => getPermissionsTeamId()]);

    $response = getJson('/_test/permission-context');

    $response->assertExactJson(['team_id' => $user->current_clinic_id]);

    expect(getPermissionsTeamId())->toBeNull();
});

it('assigns the first clinic and its permission team when no clinic is active', function (): void {
    $user = User::factory()
        ->has(
            Clinic::factory()->count(2),
            'clinics',
        )
        ->createOne();
    $clinic = $user->clinics->first();

    $user->load('currentClinic');
    login($user);
    setPermissionsTeamId(123);

    Route::middleware('web')
        ->get('/_test/permission-context', fn (): array => ['team_id' => getPermissionsTeamId()]);

    $response = getJson('/_test/permission-context');

    $response->assertExactJson(['team_id' => $clinic->id]);

    expect($user->currentClinic)
        ->is($clinic)
        ->toBeTrue()
        ->and($user->current_clinic_id)
        ->toBe($clinic->id)
        ->and(getPermissionsTeamId())
        ->toBeNull();

    assertDatabaseHas(User::class, [
        'current_clinic_id' => $clinic->id,
        'id' => $user->id,
    ]);
});

it('uses the active clinic instead of the clinic in the URL', function (): void {
    $user = User::factory()->withClinic()->createOne();
    $activeClinicId = $user->current_clinic_id;
    $urlClinic = Clinic::factory()->recycle($user)->createOne();

    login($user);

    Route::middleware('web')
        ->get('/_test/clinics/{clinic}', fn (Clinic $clinic): array => [
            'clinic_id' => $clinic->id,
            'team_id' => getPermissionsTeamId(),
        ]);

    $response = getJson('/_test/clinics/'.$urlClinic->getRouteKey());

    $response->assertForbidden();

    assertDatabaseHas(User::class, [
        'current_clinic_id' => $activeClinicId,
        'id' => $user->id,
    ]);

    expect(getPermissionsTeamId())->toBeNull();
});
```
