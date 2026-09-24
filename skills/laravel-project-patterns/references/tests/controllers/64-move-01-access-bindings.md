# Move Tests: Access and Record Binding

Pest browser PATCH reorder: guest redirect, unrelated tenant 403, foreign record 404 and soft deleted record 404. These bindings apply to both tenant-wide and within-group ordering.

Keep top-level tests in this order. `login()` creates an outsider; `login(team: ...)` supplies membership. Authenticate the URL tenant for each 404 case.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\ItemGroup;
use App\Models\Team;

it('requires authentication', function (): void {
    $itemGroup = ItemGroup::factory()->createOne();

    $response = patch(route('teams.item-groups.move', [
        'team' => $itemGroup->team,
        'item_group' => $itemGroup,
    ]));

    $response->assertRedirectToRoute('login');
});

it('prevents moving from an unrelated tenant', function (): void {
    $itemGroup = ItemGroup::factory()->createOne();

    login();

    $response = patch(route('teams.item-groups.move', [
        'team' => $itemGroup->team,
        'item_group' => $itemGroup,
    ]));

    $response->assertForbidden();
});

it('returns not found when the record belongs to another tenant', function (): void {
    $team = Team::factory()->createOne();
    $itemGroup = ItemGroup::factory()->createOne();

    login(team: $team);

    $response = patch(route('teams.item-groups.move', [
        'team' => $team,
        'item_group' => $itemGroup,
    ]));

    $response->assertNotFound();
});

it('returns not found when the record is soft deleted', function (): void {
    $itemGroup = ItemGroup::factory()->trashed()->createOne();

    login(team: $itemGroup->team);

    $response = patch(route('teams.item-groups.move', [
        'team' => $itemGroup->team,
        'item_group' => $itemGroup,
    ]));

    $response->assertNotFound();
});
```
