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
        $group = ItemGroup::factory()->recycle($workOrder->team)->createOne();
        $inactiveGroup = ItemGroup::factory()->deactivated()->recycle($workOrder->team)->createOne();
        $deletedGroup = ItemGroup::factory()->trashed()->recycle($workOrder->team)->createOne();
        $otherGroup = ItemGroup::factory()->createOne();

        login(team: $workOrder->team);

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
                    ->where('team.id', $workOrder->team->sqid)
                    ->where('workOrder.id', $workOrder->sqid)
                    ->where('currencies', CurrencyCode::options())
                    ->where('lengthUnits', LengthUnit::options())
                    ->where('weightUnits', WeightUnit::options())
                    ->where('itemGroups.0.id', $group->sqid)
                    ->where('itemGroups.0.name', $group->name)
                    ->where(
                        'itemGroups',
                        fn (Collection $options): bool => ! $options->pluck('id')
                            ->intersect([
                                $deletedGroup->sqid,
                                $inactiveGroup->sqid,
                                $otherGroup->sqid,
                            ])->isNotEmpty(),
                    )
                    ->missing('workOrder.team_id');
            });
    });
});
```
