# Requests: Create a Conditional Decimal

Require a positive increment for Up, and force it to null for None before validation. Country uniqueness belongs to the selected parent; currency, minimum weight and name remain required.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Enums\RoundingMode;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ConditionalRules;
use Illuminate\Validation\Rule;
use Override;
use Stringable;

class StorePlanRuleRequest extends FormRequest
{
    /**
     * @return array<string, list<ConditionalRules|string|Stringable>>
     */
    public function rules(): array
    {
        $servicePlan = $this->servicePlan();

        return [
            'country_code' => [
                'required',
                Rule::enum(CountryCode::class),
                Rule::unique(PlanRule::class, 'country_code')
                    ->where('service_plan_id', $servicePlan->id)
                    ->withoutTrashed(),
            ],
            'currency_code' => ['required', Rule::enum(CurrencyCode::class)],
            'minimum_chargeable_weight' => ['required', 'decimal:0,4', 'gte:0'],
            'name' => ['required', 'string', 'max:255'],
            'rounding_increment' => [
                Rule::requiredIf($this->input('rounding_mode') === RoundingMode::Up->value),
                'nullable',
                'decimal:0,4',
                'gt:0',
            ],
            'rounding_mode' => ['required', Rule::enum(RoundingMode::class)],
        ];
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        if ($this->input('rounding_mode') === RoundingMode::None->value) {
            $this->merge(['rounding_increment' => null]);
        }
    }

    private function servicePlan(): ServicePlan
    {
        $servicePlan = $this->route('service_plan');

        assert($servicePlan instanceof ServicePlan);

        return $servicePlan;
    }
}
```
