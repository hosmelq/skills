# Show Tests: Flat JSON And JSON API

Complete GET JSON show blocks distinguish the authenticated-user root id response from a public-ID JSON API resource. Preserve 401, verification 403, raw numeric ID 404, media type and data.id/type; membership is not required by the latter contract.

An existing numeric database ID is invalid on the public-ID route. It is not a fixture for a nonexistent database row.

## Current User

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\getJson;

use App\Models\User;

describe('show', function (): void {
    it('requires authentication', function (): void {
        $response = getJson(route('api.user.show'));

        $response->assertUnauthorized();
    });

    it('returns the authenticated user', function (): void {
        $user = User::factory()->createOne();

        login($user);

        $response = getJson(route('api.user.show'));

        $response->assertOk()
            ->assertJsonPath('id', $user->public_id);
    });
});
```

## Public Resource

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\getJson;

use App\Models\Team;
use App\Models\User;

describe('show', function (): void {
    it('requires authentication', function (): void {
        $team = Team::factory()->createOne();

        $response = getJson(route('api.teams.show', $team->public_id));

        $response->assertUnauthorized();
    });

    it('prevents viewing for an unverified user', function (): void {
        $team = Team::factory()->createOne();
        $user = User::factory()->unverified()->createOne();

        login($user);

        $response = getJson(route('api.teams.show', $team->public_id));

        $response->assertForbidden();
    });

    it('returns not found for an invalid public identifier', function (): void {
        $team = Team::factory()->createOne();

        login();

        $response = getJson(route('api.teams.show', $team->id));

        $response->assertNotFound();
    });

    it('returns the resource', function (): void {
        $team = Team::factory()->createOne();

        login();

        $response = getJson(route('api.teams.show', $team->public_id));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonPath('data.id', $team->public_id)
            ->assertJsonPath('data.type', 'teams');
    });
});
```
