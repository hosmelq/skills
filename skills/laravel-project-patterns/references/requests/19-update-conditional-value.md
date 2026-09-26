# Requests: Update an Effective Conditional Decimal

Use stored mode only when input is omitted. None clears the increment; Up fills a missing increment from storage. Explicit null is preserved and fails the positive required increment rule. Ignore the bound child in parent-scoped uniqueness.

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

class UpdatePlanRuleRequest extends FormRequest
{
    /**
     * @return array<string, list<ConditionalRules|string|Stringable>>
     */
    public function rules(): array
    {
        $servicePlan = $this->servicePlan();
        $planRule = $this->planRule();

        return [
            'country_code' => [
                'sometimes',
                'required',
                Rule::enum(CountryCode::class),
                Rule::unique(PlanRule::class, 'country_code')
                    ->ignore($planRule)
                    ->where('service_plan_id', $servicePlan->id)
                    ->withoutTrashed(),
            ],
            'currency_code' => ['sometimes', 'required', Rule::enum(CurrencyCode::class)],
            'minimum_chargeable_weight' => ['sometimes', 'required', 'decimal:0,4', 'gte:0'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'rounding_increment' => [
                Rule::requiredIf($this->roundingMode($planRule) === RoundingMode::Up->value),
                'nullable',
                'decimal:0,4',
                'gt:0',
            ],
            'rounding_mode' => [
                'sometimes',
                'required',
                Rule::enum(RoundingMode::class),
            ],
        ];
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        $planRule = $this->planRule();
        $roundingMode = $this->roundingMode($planRule);

        if ($roundingMode === RoundingMode::None->value) {
            $this->merge(['rounding_increment' => null]);

            return;
        }

        if ($roundingMode === RoundingMode::Up->value) {
            $this->mergeIfMissing([
                'rounding_increment' => $planRule->rounding_increment,
            ]);
        }
    }

    private function planRule(): PlanRule
    {
        $planRule = $this->route('plan_rule');

        assert($planRule instanceof PlanRule);

        return $planRule;
    }

    private function roundingMode(PlanRule $planRule): null|string
    {
        $roundingMode = $this->input('rounding_mode', $planRule->rounding_mode->value);

        return is_string($roundingMode) ? $roundingMode : null;
    }

    private function servicePlan(): ServicePlan
    {
        $servicePlan = $this->route('service_plan');

        assert($servicePlan instanceof ServicePlan);

        return $servicePlan;
    }
}
```
