# Update Tests: Access Guest Root

Pest PATCH update: Browser guest authentication on tenant settings and direct-record routes. Adapt the route, model and valid payload to the endpoint; both cases redirect to login.

## Tenant settings

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Team;

describe('update', function (): void {
    it('requires authentication', function (): void {
        $team = Team::factory()->createOne();

        $response = patch(route('teams.update', [
            'team' => $team,
        ]));

        $response->assertRedirectToRoute('login');
    });
});
```

## Direct record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\ItemGroup;

describe('update', function (): void {
    it('requires authentication', function (): void {
        $itemGroup = ItemGroup::factory()->createOne();

        $response = patch(route('teams.item-groups.update', [
            'team' => $itemGroup->team,
            'item_group' => $itemGroup,
        ]), [
            'name' => 'Computers',
        ]);

        $response->assertRedirectToRoute('login');
    });
});
```
