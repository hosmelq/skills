# Update Tests: Bindings Foreign Record

PATCH update: A direct foreign-tenant record returns 404 with the URL tenant authenticated.

## Foreign tenant record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\ItemGroup;
use App\Models\Team;

describe('update', function (): void {
    it('returns not found when the record belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $itemGroup = ItemGroup::factory()->createOne();

        login(team: $team);

        $response = patch(route('teams.item-groups.update', [
            'team' => $team,
            'item_group' => $itemGroup,
        ]), [
            'name' => 'Computers',
        ]);

        $response->assertNotFound();
    });
});
```
