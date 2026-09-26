# Requests: Create Scoped Related Selections

Validate tenant-owned, nontrashed selections and complete measurement groups. Cabinet, facilities, service plan and status must also be active; a plan rule needs an active parent plan. Matching selected relations, defaults and persistence are action contracts; these field rules alone do not enforce all of them.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\BaseStatus;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\Cabinet;
use App\Models\Facility;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class StoreWorkOrderRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable|ValidationRule>>
     */
    public function rules(#[RouteParameter('team')] Team $team): array
    {
        return [
            'cabinet_id' => [
                'nullable',
                Rule::exists(Cabinet::class, 'id')
                    ->where('team_id', $team->id)
                    ->whereNull('deactivated_at')
                    ->withoutTrashed(),
            ],
            'current_facility_id' => [
                'nullable',
                Rule::exists(Facility::class, 'id')
                    ->where('team_id', $team->id)
                    ->whereNull('deactivated_at')
                    ->withoutTrashed(),
            ],
            'dimension_unit' => [
                'nullable',
                'required_with:height,length,width',
                Rule::enum(LengthUnit::class),
            ],
            'external_carrier_name' => ['nullable', 'string', 'max:255'],
            'external_tracking_number' => ['nullable', 'string', 'max:255'],
            'height' => [
                'nullable',
                'required_with:dimension_unit,length,width',
                'decimal:0,4',
                'gt:0',
                'max:9999.9999',
            ],
            'length' => [
                'nullable',
                'required_with:dimension_unit,height,width',
                'decimal:0,4',
                'gt:0',
                'max:9999.9999',
            ],
            'member_id' => [
                'nullable',
                Rule::exists(Member::class, 'id')
                    ->where('team_id', $team->id)
                    ->withoutTrashed(),
            ],
            'note' => ['nullable', 'string', 'max:2000'],
            'pickup_facility_id' => [
                'nullable',
                Rule::exists(Facility::class, 'id')
                    ->where('team_id', $team->id)
                    ->whereNull('deactivated_at')
                    ->withoutTrashed(),
            ],
            'plan_rule_id' => [
                'nullable',
                Rule::exists(PlanRule::class, 'id')
                    ->where('team_id', $team->id)
                    ->where(function (Builder $builder) use ($team): void {
                        $builder->whereIn(
                            'service_plan_id',
                            $team->servicePlans()->active()->select('id'),
                        );
                    })
                    ->withoutTrashed(),
            ],
            'received_at' => ['nullable', 'date'],
            'received_facility_id' => [
                'nullable',
                Rule::exists(Facility::class, 'id')
                    ->where('team_id', $team->id)
                    ->whereNull('deactivated_at')
                    ->withoutTrashed(),
            ],
            'received_label_text' => ['nullable', 'string', 'max:5000'],
            'reference' => ['nullable', 'string', 'max:255'],
            'service_plan_id' => [
                'nullable',
                Rule::exists(ServicePlan::class, 'id')
                    ->where('team_id', $team->id)
                    ->whereNull('deactivated_at')
                    ->withoutTrashed(),
            ],
            'weight' => [
                'nullable',
                'required_with:weight_unit',
                'decimal:0,4',
                'gt:0',
                'max:9999.9999',
            ],
            'weight_unit' => ['nullable', 'required_with:weight', Rule::enum(WeightUnit::class)],
            'width' => [
                'nullable',
                'required_with:dimension_unit,height,length',
                'decimal:0,4',
                'gt:0',
                'max:9999.9999',
            ],
            'work_order_status_id' => [
                'nullable',
                Rule::exists(WorkOrderStatus::class, 'id')
                    ->where('team_id', $team->id)
                    ->where('base_status', BaseStatus::Open->value)
                    ->whereNull('deactivated_at')
                    ->withoutTrashed(),
            ],
        ];
    }
}
```
