# Controllers: Group Form Properties

Combine a property group with explicit page props. Keep member display sorting, active relations, owner predicates, required live parents, eager loads and each option order. Closures defer evaluation until the prop is resolved; they are not deferred-response props. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\BaseStatus;
use App\Http\Inertia\WorkOrders\WorkOrderFormProps;
use App\Http\Resources\WorkOrderStatusResource;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderController
{
    public function create(Team $team): Response
    {
        $statuses = $team->workOrderStatuses()
            ->where('base_status', BaseStatus::Open)
            ->whereNull('deactivated_at')
            ->ordered()
            ->get();

        return Inertia::render('work-orders/Create', [
            new WorkOrderFormProps($team),
            'statuses' => WorkOrderStatusResource::collection($statuses),
            'team' => $team->toResource(),
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Inertia\WorkOrders;

use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Http\Resources\CabinetResource;
use App\Http\Resources\FacilityResource;
use App\Http\Resources\MemberResource;
use App\Http\Resources\PlanRuleResource;
use App\Http\Resources\ServicePlanResource;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Override;

class WorkOrderFormProps implements ProvidesInertiaProperties
{
    public function __construct(private readonly Team $team)
    {
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toInertiaProperties(RenderContext $context): iterable
    {
        return [
            'cabinets' => function (): AnonymousResourceCollection {
                $cabinets = $this->team->cabinets()
                    ->active()
                    ->whereHas('member')
                    ->whereHas('servicePlan', function (Builder $builder): void {
                        $builder->whereNull('deactivated_at');
                    })
                    ->with(['member', 'servicePlan'])
                    ->orderBy('code')
                    ->get();

                return CabinetResource::collection($cabinets);
            },
            'facilities' => function (): AnonymousResourceCollection {
                $facilities = $this->team->facilities()
                    ->active()
                    ->orderBy('name')
                    ->get();

                return FacilityResource::collection($facilities);
            },
            'lengthUnits' => LengthUnit::options(),
            'members' => function (): AnonymousResourceCollection {
                $members = $this->team->members()
                    ->get()
                    ->sortBy(fn (Member $member): string => $member->display_name)
                    ->values();

                return MemberResource::collection($members);
            },
            'planRules' => function (): AnonymousResourceCollection {
                $planRules = PlanRule::query()
                    ->where('team_id', $this->team->id)
                    ->whereHas('servicePlan', function (Builder $builder): void {
                        $builder->where('team_id', $this->team->id)
                            ->whereNull('deactivated_at');
                    })
                    ->with('servicePlan')
                    ->orderBy('name')
                    ->get();

                return PlanRuleResource::collection($planRules);
            },
            'servicePlans' => function (): AnonymousResourceCollection {
                $servicePlans = $this->team->servicePlans()
                    ->active()
                    ->orderBy('name')
                    ->get();

                return ServicePlanResource::collection($servicePlans);
            },
            'weightUnits' => WeightUnit::options(),
        ];
    }
}
```
