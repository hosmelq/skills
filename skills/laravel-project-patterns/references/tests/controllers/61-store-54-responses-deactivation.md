# Store Tests: Responses Deactivation

Pest POST store: Three complete success patterns: generic redirect and toast, exact collection redirect, and deeper existing-record binding. Only entity-label-equivalent generic two-binding successes share a representative.

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
        $workOrderItemGroup = ItemGroup::factory()->createOne();

        signIn(team: $workOrderItemGroup->team);

        mock(DeactivateItemGroup::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                ItemGroup $workOrderItemGroupArgument
            ): bool => $workOrderItemGroupArgument->is($workOrderItemGroup));

        $response = post(route('teams.item-groups.deactivation.store', [
            'team' => $workOrderItemGroup->team,
            'item_group' => $workOrderItemGroup,
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

        signIn(team: $facility->team);

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

        signIn(team: $cabinet->member->team);

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
