# Requests: Create Integer Bounds

Require the lower bound, permit a nullable upper bound and compare in both directions when applicable. Bounds may be equal. Scope the name to the bound tenant; enum units remain required.

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
use Stringable;

class StoreServicePlanRequest extends FormRequest
{
    /**
     * @return array<string, list<ConditionalRules|string|Stringable>>
     */
    public function rules(): array
    {
        $team = $this->team();

        return [
            'description' => ['nullable', 'string', 'max:2000'],
            'estimated_transit_time_unit' => ['required', Rule::enum(TransitTimeUnit::class)],
            'maximum_estimated_transit_time' => [
                'nullable',
                'int',
                'gte:0',
                'gte:minimum_estimated_transit_time',
            ],
            'minimum_estimated_transit_time' => [
                'required',
                'int',
                'gte:0',
                Rule::when(
                    $this->filled('maximum_estimated_transit_time'),
                    'lte:maximum_estimated_transit_time'
                ),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(ServicePlan::class)
                    ->where('team_id', $team->id)
                    ->withoutTrashed(),
            ],
            'weight_unit' => ['required', Rule::enum(WeightUnit::class)],
        ];
    }

    private function team(): Team
    {
        $team = $this->route('team');

        assert($team instanceof Team);

        return $team;
    }
}
```
