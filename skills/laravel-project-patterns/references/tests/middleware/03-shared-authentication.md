# Middleware Tests: Shared Authentication Props

Inertia middleware feature tests assert null authentication props for guests and the selected tenant/user `sqid` values for authenticated requests. These are selected prop assertions, not a full resource serialization check.

`login(team: $team)` selects the supplied team and returns the authenticated user. Use an existing Inertia page and the suite’s view setup.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Team;
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
                ->where('auth.team', null)
                ->where('auth.user', null);
        });
});

it('shares authenticated tenant and user IDs', function (): void {
    Route::middleware(HandleInertiaRequests::class)
        ->get('/_test', fn () => Inertia::render('auth/Login'));

    $team = Team::factory()->createOne();

    $user = login(team: $team);

    $response = get('/_test');

    $response->assertOk()
        ->assertInertia(function (AssertableInertia $page) use ($team, $user): void {
            $page
                ->where('auth.team.id', $team->sqid)
                ->where('auth.user.id', $user->sqid);
        });
});
```
