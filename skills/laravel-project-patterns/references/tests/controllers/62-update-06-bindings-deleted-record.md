# Update Tests: Bindings Deleted Record

PATCH update: A soft-deleted direct record returns 404 with its URL tenant authenticated.

## Soft deleted record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\ItemGroup;

describe('update', function (): void {
    it('returns not found when the record is soft deleted', function (): void {
        $itemGroup = ItemGroup::factory()->trashed()->createOne();

        login(team: $itemGroup->team);

        $response = patch(route('teams.item-groups.update', [
            'team' => $itemGroup->team,
            'item_group' => $itemGroup,
        ]), [
            'name' => 'Computers',
        ]);

        $response->assertNotFound();
    });
});
```
