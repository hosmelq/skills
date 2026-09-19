# Create Tests: Category Options And Parent Payload

Positive GET create-page test for category options: allowed IDs and labels, inactive/deleted/foreign exclusions, complete enum options, mutation enabled, public parent identifiers, and absence of an internal ownership key.

Use `shows the create page`. The exclusion assertion must name the ineligible fixtures; merely creating them does not prove coverage.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\ItemCategory;
use App\Models\WorkOrder;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('shows the create page', function (): void {
        $workOrder = WorkOrder::factory()->createOne();
        $category = ItemCategory::factory()->for($workOrder->team)->createOne();
        $inactiveCategory = ItemCategory::factory()
            ->deactivated()
            ->for($workOrder->team)
            ->createOne();
        $deletedCategory = ItemCategory::factory()
            ->trashed()
            ->for($workOrder->team)
            ->createOne();
        $otherCategory = ItemCategory::factory()->createOne();

        signIn(team: $workOrder->team);

        $response = get(route('teams.work-orders.items.create', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use (
                $category,
                $deletedCategory,
                $inactiveCategory,
                $otherCategory,
                $workOrder,
            ): void {
                $page->component('work-orders/items/Create')
                    ->where('canMutate', true)
                    ->where('team.id', $workOrder->team->public_id)
                    ->where('workOrder.id', $workOrder->public_id)
                    ->where('currencies', CurrencyCode::options())
                    ->where('lengthUnits', LengthUnit::options())
                    ->where('weightUnits', WeightUnit::options())
                    ->where('itemCategories.0.id', $category->public_id)
                    ->where('itemCategories.0.name', $category->name)
                    ->where(
                        'itemCategories',
                        fn (Collection $options): bool => ! $options->pluck('id')
                            ->intersect([
                                $deletedCategory->public_id,
                                $inactiveCategory->public_id,
                                $otherCategory->public_id,
                            ])->isNotEmpty(),
                    )
                    ->missing('workOrder.team_id');
            });
    });
});
```
