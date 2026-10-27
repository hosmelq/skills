# API: Token Authenticated Endpoint

Serve the signed-in API user with a resource behind `auth:sanctum`, reading the user with `#[CurrentUser]`.

- Name API routes with the `api.` prefix.

```php
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
```

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

    it('can see the authenticated user', function (): void {
        $user = User::factory()->createOne();

        login($user);

        $response = getJson(route('api.user.show'));

        $response->assertOk()->assertJsonPath('id', $user->sqid);
    });
});
```
