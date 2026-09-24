# Update Tests: Bindings Foreign Record

Pest PATCH update: A direct record from another tenant returns 404. Authenticate the URL tenant independently of the foreign record; adapt the model, route and valid payload to the endpoint.

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

        signIn(team: $team);

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
