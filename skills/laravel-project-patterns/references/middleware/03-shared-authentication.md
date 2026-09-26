# Middleware: Shared Authentication

Preserve parent shared props, then add `auth.team` and `auth.user` as resources. Guests receive two nulls; a user without a current team receives a null team and a user resource. Resource construction is eager; serialization belongs to Inertia and the resources.

Use the inspected `currentTeam` relation and `toResource()` implementations. Resolving the current team may query or select and persist a default team; keep that behavior in the model.

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Middleware;
use Override;

class HandleInertiaRequests extends Middleware
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
     * @return array{team: null|JsonResource, user: null|JsonResource}
     */
    private function authProperties(Request $request): array
    {
        $user = $request->user();

        return [
            'team' => $user?->currentTeam?->toResource(),
            'user' => $user?->toResource(),
        ];
    }
}
```
