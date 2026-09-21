# Store Tests: Access Guest Root

Pest POST store: Web guest; no bound tenant, tenant-only empty request, or tenant-only request with a name payload.

## Requires authentication — variant 1

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

## Requires authentication — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Team;

describe('store', function (): void {
    it('requires authentication', function (): void {
        $team = Team::factory()->createOne();

        $response = post(route('teams.facilities.store', [
            'team' => $team,
        ]));

        $response->assertRedirectToRoute('login');
    });
});
```

## Requires authentication — variant 3

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
