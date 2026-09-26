# Controllers: Return a Bound or Authenticated Resource

Return the resource directly for a route-bound record or the injected current user. Apply the inspected authentication, verification and binding middleware in routes.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;

class AuthenticatedUserController
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

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\TeamResource;
use App\Models\Team;

class TeamController
{
    public function show(Team $team): TeamResource
    {
        return TeamResource::make($team);
    }
}
```
