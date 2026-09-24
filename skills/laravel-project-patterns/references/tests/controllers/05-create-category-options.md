# Create Tests: Group Options And Parent Payload

Positive GET create-page test for group options: allowed IDs and labels, inactive/deleted/foreign exclusions, complete enum options, mutation enabled, public parent identifiers, and absence of an internal ownership key.

Use `shows the create page`. The exclusion assertion must name the ineligible fixtures; merely creating them does not prove coverage.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\ItemGroup;
use App\Models\WorkOrder;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('shows the create page', function (): void {
        $workOrder = WorkOrder::factory()->createOne();
        $group = ItemGroup::factory()->for($workOrder->team)->createOne();
        $inactiveGroup = ItemGroup::factory()
            ->deactivated()
            ->for($workOrder->team)
            ->createOne();
        $deletedGroup = ItemGroup::factory()
            ->trashed()
            ->for($workOrder->team)
            ->createOne();
        $otherGroup = ItemGroup::factory()->createOne();

        signIn(team: $workOrder->team);

        $response = get(route('teams.work-orders.lines.create', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use (
                $group,
                $deletedGroup,
                $inactiveGroup,
                $otherGroup,
                $workOrder,
            ): void {
                $page->component('work-orders/lines/Create')
                    ->where('canMutate', true)
                    ->where('team.id', $workOrder->team->public_id)
                    ->where('workOrder.id', $workOrder->public_id)
                    ->where('currencies', CurrencyCode::options())
                    ->where('lengthUnits', LengthUnit::options())
                    ->where('weightUnits', WeightUnit::options())
                    ->where('itemGroups.0.id', $group->public_id)
                    ->where('itemGroups.0.name', $group->name)
                    ->where(
                        'itemGroups',
                        fn (Collection $options): bool => ! $options->pluck('id')
                            ->intersect([
                                $deletedGroup->public_id,
                                $inactiveGroup->public_id,
                                $otherGroup->public_id,
                            ])->isNotEmpty(),
                    )
                    ->missing('workOrder.team_id');
            });
    });
});
```
