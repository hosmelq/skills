# Store Tests: Access Guest Root

POST store: Browser guest authentication on routes with no bindings or a tenant binding. Adapt the route, model and valid payload to the endpoint; both cases redirect to login.

## No route bindings

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

describe('store', function (): void {
    it('requires authentication', function (): void {
        $response = post(route('teams.store'));

        $response->assertRedirectToRoute('login');
    });
});
```

## Tenant route binding

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Team;

describe('store', function (): void {
    it('requires authentication', function (): void {
        $team = Team::factory()->createOne();

        $response = post(route('teams.item-groups.store', [
            'team' => $team,
        ]), [
            'name' => 'Electronics',
        ]);

        $response->assertRedirectToRoute('login');
    });
});
```
