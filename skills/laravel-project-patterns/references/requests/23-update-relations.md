# Requests: Update Historical Selections

Group current historical IDs inside the tenant predicate. New selections must meet the shown active/nontrashed conditions; current selections may be retained. This request intentionally leaves effective measurement groups and relation consistency to the action, and includes no state/current-facility field in validated output.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\Cabinet;
use App\Models\Facility;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class UpdateWorkOrderRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable|ValidationRule>>
     */
    public function rules(
        #[RouteParameter('team')] Team $team,
        #[RouteParameter('work_order')] WorkOrder $workOrder,
    ): array {
        return [
            'cabinet_id' => [
                'sometimes',
                'nullable',
                Rule::exists(Cabinet::class, 'id')
                    ->where('team_id', $team->id)
                    ->where(function (Builder $builder) use ($workOrder): void {
                        $builder->where(function (Builder $builder): void {
                            $builder->whereNull('deactivated_at')->whereNull('deleted_at');
                        });

                        if ($workOrder->cabinet_id !== null) {
                            $builder->orWhere('id', $workOrder->cabinet_id);
                        }
                    }),
            ],
            'dimension_unit' => ['sometimes', 'nullable', Rule::enum(LengthUnit::class)],
            'external_carrier_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'external_tracking_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'height' => ['sometimes', 'nullable', 'decimal:0,4', 'gt:0', 'max:9999.9999'],
            'length' => ['sometimes', 'nullable', 'decimal:0,4', 'gt:0', 'max:9999.9999'],
            'member_id' => [
                'sometimes',
                'nullable',
                Rule::exists(Member::class, 'id')
                    ->where('team_id', $team->id)
                    ->where(function (Builder $builder) use ($workOrder): void {
                        $builder->whereNull('deleted_at');

                        if ($workOrder->member_id !== null) {
                            $builder->orWhere('id', $workOrder->member_id);
                        }
                    }),
            ],
            'note' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'pickup_facility_id' => [
                'sometimes',
                'nullable',
                Rule::exists(Facility::class, 'id')
                    ->where('team_id', $team->id)
                    ->where(function (Builder $builder) use ($workOrder): void {
                        $builder->where(function (Builder $builder): void {
                            $builder->whereNull('deactivated_at')->whereNull('deleted_at');
                        });

                        if ($workOrder->pickup_facility_id !== null) {
                            $builder->orWhere('id', $workOrder->pickup_facility_id);
                        }
                    }),
            ],
            'plan_rule_id' => [
                'sometimes',
                'nullable',
                Rule::exists(PlanRule::class, 'id')
                    ->where('team_id', $team->id)
                    ->where(function (Builder $builder) use ($workOrder): void {
                        $builder->whereNull('deleted_at');

                        if ($workOrder->plan_rule_id !== null) {
                            $builder->orWhere('id', $workOrder->plan_rule_id);
                        }
                    }),
            ],
            'received_at' => ['sometimes', 'required', 'date'],
            'received_facility_id' => [
                'sometimes',
                'nullable',
                Rule::exists(Facility::class, 'id')
                    ->where('team_id', $team->id)
                    ->where(function (Builder $builder) use ($workOrder): void {
                        $builder->where(function (Builder $builder): void {
                            $builder->whereNull('deactivated_at')->whereNull('deleted_at');
                        });

                        if ($workOrder->received_facility_id !== null) {
                            $builder->orWhere('id', $workOrder->received_facility_id);
                        }
                    }),
            ],
            'received_label_text' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'service_plan_id' => [
                'sometimes',
                'nullable',
                Rule::exists(ServicePlan::class, 'id')
                    ->where('team_id', $team->id)
                    ->where(function (Builder $builder) use ($workOrder): void {
                        $builder->where(function (Builder $builder): void {
                            $builder->whereNull('deactivated_at')->whereNull('deleted_at');
                        });

                        if ($workOrder->service_plan_id !== null) {
                            $builder->orWhere('id', $workOrder->service_plan_id);
                        }
                    }),
            ],
            'weight' => ['sometimes', 'nullable', 'decimal:0,4', 'gt:0', 'max:9999.9999'],
            'weight_unit' => ['sometimes', 'nullable', Rule::enum(WeightUnit::class)],
            'width' => ['sometimes', 'nullable', 'decimal:0,4', 'gt:0', 'max:9999.9999'],
        ];
    }
}
```
