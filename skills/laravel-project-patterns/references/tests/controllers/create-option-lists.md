# Create Tests: Active Same-Tenant Options Ordered By Name

Use for a GET create page that supplies related records as form options. Keep allowed records and excluded records in the fixture so the response proves the scope, availability and display order. The workshop domain and its data are fictional. Factories, routes, `signIn(team: ...)` and `public_id` illustrate a project contract; they are not Laravel defaults. Adapt them to the current project and keep its actual test root.

## 1. Ordered service plans

In the enrollment controller, place the valid ordered-list test before the unavailable-options test. The first establishes the exact two allowed options and their order, excluding an option owned by another team. The second proves exclusion of deactivated and soft-deleted plans.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Member;
use App\Models\ServicePlan;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('shows the create page', function (): void {
        $member = Member::factory()->createOne();
        $secondServicePlan = ServicePlan::factory()
            ->for($member->team)
            ->createOne(['name' => 'Zulu Plan']);
        $firstServicePlan = ServicePlan::factory()
            ->for($member->team)
            ->createOne(['name' => 'Alpha Plan']);
        ServicePlan::factory()->createOne(['name' => 'Other team']);

        signIn(team: $member->team);

        $response = get(route('teams.members.enrollments.create', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use (
                $member,
                $firstServicePlan,
                $secondServicePlan,
            ): void {
                $page->component('members/enrollments/Create')
                    ->where('member.id', $member->public_id)
                    ->where('team.id', $member->team->public_id)
                    ->has('servicePlans', 2)
                    ->where('servicePlans.0.id', $firstServicePlan->public_id)
                    ->where('servicePlans.0.name', 'Alpha Plan')
                    ->where('servicePlans.1.id', $secondServicePlan->public_id)
                    ->where('servicePlans.1.name', 'Zulu Plan');
            });
    });

    it('shows the create page without unavailable service plans', function (): void {
        $member = Member::factory()->createOne();

        ServicePlan::factory()
            ->deactivated()
            ->for($member->team)
            ->createOne();
        ServicePlan::factory()
            ->trashed()
            ->for($member->team)
            ->createOne();

        signIn(team: $member->team);

        $response = get(route('teams.members.enrollments.create', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page): void {
                $page->component('members/enrollments/Create')
                    ->has('servicePlans', 0);
            });
    });
});
```

## 2. Active categories and parent payload

This is a separate controller test file. Preserve its complete page contract: active category identity and label, exclusions by category ID, enum options, `canMutate=true` and absence of the internal parent team key. A valid component alone does not establish these behaviors.

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

## Related References

- [Create block](create.md)
