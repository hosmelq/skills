# Requests: Update Effective Integer Bounds

When a filled bound is supplied, merge its stored counterpart only under the shown missing/non-null guards. Explicit null upper bounds remain null; unrelated edits do not receive both bounds automatically. Merged values enter validated output.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TransitTimeUnit;
use App\Enums\WeightUnit;
use App\Models\ServicePlan;
use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ConditionalRules;
use Illuminate\Validation\Rule;
use Override;
use Stringable;

class UpdateServicePlanRequest extends FormRequest
{
    /**
     * @return array<string, list<ConditionalRules|string|Stringable>>
     */
    public function rules(): array
    {
        $team = $this->team();
        $servicePlan = $this->servicePlan();

        return [
            'description' => ['nullable', 'string', 'max:2000'],
            'estimated_transit_time_unit' => [
                'sometimes',
                'required',
                Rule::enum(TransitTimeUnit::class),
            ],
            'maximum_estimated_transit_time' => [
                'sometimes',
                'nullable',
                'int',
                'gte:0',
                'gte:minimum_estimated_transit_time',
            ],
            'minimum_estimated_transit_time' => [
                'sometimes',
                'required',
                'int',
                'gte:0',
                Rule::when(
                    $this->filled('maximum_estimated_transit_time'),
                    'lte:maximum_estimated_transit_time'
                ),
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique(ServicePlan::class)
                    ->ignore($servicePlan)
                    ->where('team_id', $team->id)
                    ->withoutTrashed(),
            ],
            'weight_unit' => [
                'sometimes',
                'required',
                Rule::enum(WeightUnit::class),
            ],
        ];
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        $servicePlan = $this->servicePlan();

        if (
            $this->filled('minimum_estimated_transit_time')
            && $this->missing('maximum_estimated_transit_time')
            && $servicePlan->maximum_estimated_transit_time !== null
        ) {
            $this->merge([
                'maximum_estimated_transit_time' => $servicePlan->maximum_estimated_transit_time,
            ]);
        }

        if (
            $this->filled('maximum_estimated_transit_time')
            && $this->missing('minimum_estimated_transit_time')
        ) {
            $this->merge([
                'minimum_estimated_transit_time' => $servicePlan->minimum_estimated_transit_time,
            ]);
        }
    }

    private function servicePlan(): ServicePlan
    {
        $servicePlan = $this->route('service_plan');

        assert($servicePlan instanceof ServicePlan);

        return $servicePlan;
    }

    private function team(): Team
    {
        $team = $this->route('team');

        assert($team instanceof Team);

        return $team;
    }
}
```
