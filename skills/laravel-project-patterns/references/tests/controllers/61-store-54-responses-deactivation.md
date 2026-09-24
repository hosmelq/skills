# Store Tests: Responses Deactivation

Pest POST store: Three complete success patterns: generic redirect and toast, exact collection redirect, and deeper existing-record binding. The mocked action proves delegation and response mapping; it does not prove persisted deactivation.

## Deactivates the record — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\ItemGroups\DeactivateItemGroup;
use App\Models\ItemGroup;

describe('store', function (): void {
    it('deactivates the record', function (): void {
        $itemGroup = ItemGroup::factory()->createOne();

        login(team: $itemGroup->team);

        mock(DeactivateItemGroup::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                ItemGroup $itemGroupArgument
            ): bool => $itemGroupArgument->is($itemGroup));

        $response = post(route('teams.item-groups.deactivation.store', [
            'team' => $itemGroup->team,
            'item_group' => $itemGroup,
        ]));

        $response->assertRedirect()
            ->assertToast('Item group deactivated');
    });
});
```

## Deactivates the record — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\Facilities\DeactivateFacility;
use App\Models\Facility;

describe('store', function (): void {
    it('deactivates the record', function (): void {
        $facility = Facility::factory()->createOne();

        login(team: $facility->team);

        mock(DeactivateFacility::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Facility $facilityArgument): bool => $facilityArgument->is($facility));

        $response = post(route('teams.facilities.deactivation.store', [
            'team' => $facility->team,
            'facility' => $facility,
        ]));

        $response->assertRedirectToRoute('teams.facilities.index', [
            'team' => $facility->team,
        ])
            ->assertToast('Facility deactivated');
    });
});
```

## Deactivates the record — variant 3

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\Cabinets\DeactivateCabinet;
use App\Models\Cabinet;

describe('store', function (): void {
    it('deactivates the record', function (): void {
        $cabinet = Cabinet::factory()->createOne();

        login(team: $cabinet->member->team);

        mock(DeactivateCabinet::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Cabinet $cabinetArgument): bool => $cabinetArgument->is($cabinet));

        $response = post(route('teams.members.cabinets.deactivation.store', [
            'team' => $cabinet->member->team,
            'member' => $cabinet->member,
            'cabinet' => $cabinet,
        ]));

        $response->assertRedirect()
            ->assertToast('Cabinet deactivated');
    });
});
```
