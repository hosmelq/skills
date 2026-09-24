# Move Tests: Predecessor Validation

Pest browser PATCH reorder: named exists dataset, self-reference, foreign tenant, deleted predecessor and nonexistent raw public ID. Preserve redirect-back field errors and valid route bindings.

Order: field dataset, self-reference, foreign tenant, soft deleted predecessor, nonexistent predecessor. The encoded missing ID and raw nonexistent token are separate fixtures; keep both.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\ItemGroup;
use App\Support\PublicId;

it('validates fields', function (array $data, array $expected): void {
    $itemGroup = ItemGroup::factory()->createOne();

    login(team: $itemGroup->team);

    $response = patch(route('teams.item-groups.move', [
        'team' => $itemGroup->team,
        'item_group' => $itemGroup,
    ]), $data);

    $response->assertRedirectBackWithErrors($expected);
})->with([
    'exists' => [
        'data' => fn (): array => [
            'move_after_id' => resolve(PublicId::class)->encode(PHP_INT_MAX),
        ],
        'expected' => [
            'move_after_id' => 'The selected move after id is invalid.',
        ],
    ],
]);

it('rejects moving a record after itself', function (): void {
    $itemGroup = ItemGroup::factory()->createOne();

    login(team: $itemGroup->team);

    $response = patch(route('teams.item-groups.move', [
        'team' => $itemGroup->team,
        'item_group' => $itemGroup,
    ]), [
        'move_after_id' => $itemGroup->public_id,
    ]);

    $response->assertRedirectBackWithErrors([
        'move_after_id' => 'The selected move after id is invalid.',
    ]);
});

it('rejects a predecessor from another tenant', function (): void {
    $itemGroup = ItemGroup::factory()->createOne();
    $moveAfterItemGroup = ItemGroup::factory()->createOne();

    login(team: $itemGroup->team);

    $response = patch(route('teams.item-groups.move', [
        'team' => $itemGroup->team,
        'item_group' => $itemGroup,
    ]), [
        'move_after_id' => $moveAfterItemGroup->public_id,
    ]);

    $response->assertRedirectBackWithErrors([
        'move_after_id' => 'The selected move after id is invalid.',
    ]);
});

it('rejects a soft deleted predecessor', function (): void {
    $itemGroup = ItemGroup::factory()->createOne();
    $moveAfterItemGroup = ItemGroup::factory()->trashed()->recycle($itemGroup->team)->createOne();

    login(team: $itemGroup->team);

    $response = patch(route('teams.item-groups.move', [
        'team' => $itemGroup->team,
        'item_group' => $itemGroup,
    ]), [
        'move_after_id' => $moveAfterItemGroup->public_id,
    ]);

    $response->assertRedirectBackWithErrors([
        'move_after_id' => 'The selected move after id is invalid.',
    ]);
});

it('rejects a nonexistent predecessor', function (): void {
    $itemGroup = ItemGroup::factory()->createOne();

    login(team: $itemGroup->team);

    $response = patch(route('teams.item-groups.move', [
        'team' => $itemGroup->team,
        'item_group' => $itemGroup,
    ]), [
        'move_after_id' => 'abcdefghij',
    ]);

    $response->assertRedirectBackWithErrors([
        'move_after_id' => 'The selected move after id is invalid.',
    ]);
});
```
