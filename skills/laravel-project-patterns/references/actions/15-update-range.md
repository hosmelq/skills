# Actions: Update Effective Range Bounds

Update range bounds using persisted values for Optional and explicit null for open-ended maximum. Exclude the current row in both uniqueness and overlap checks; verify the active ancestor first.

Use the inspected numeric validation and database constraints; these preflight queries do not prove concurrency behavior.

```php
<?php

declare(strict_types=1);

namespace App\Actions\ServicePlans;

use App\Actions\ServicePlans\Inputs\UpdatePlanRateInput;
use App\Exceptions\CannotUpdatePlanRate;
use App\Models\PlanRate;
use App\Models\PlanRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;

class UpdatePlanRate
{
    public function __construct(private readonly EnsureActiveServicePlan $ensureActiveServicePlan)
    {
    }

    public function handle(PlanRate $rate, UpdatePlanRateInput $input): void
    {
        DB::transaction(function () use ($input, $rate): void {
            $planRule = $rate->planRule()->firstOrFail();
            $this->ensureActiveServicePlan->handle($planRule->servicePlan()->firstOrFail());

            $this->ensureRangeIsAvailable($planRule, $rate, $input);

            $rate->update($input->transform());
        });
    }

    private function ensureRangeIsAvailable(
        PlanRule $planRule,
        PlanRate $rate,
        UpdatePlanRateInput $input,
    ): void {
        $minimumWeight = $input->minimumWeight instanceof Optional
            ? (float) $rate->minimum_weight
            : (float) $input->minimumWeight;
        $maximumWeight = $this->maximumWeight($rate, $input);

        if ($maximumWeight === null) {
            $openEndedRateExists = $planRule->rates()
                ->whereKeyNot($rate)
                ->whereNull('maximum_weight')
                ->exists();

            if ($openEndedRateExists) {
                throw CannotUpdatePlanRate::becauseItHasAnOpenEndedRate();
            }
        }

        $overlappingRateExists = $planRule->rates()
            ->whereKeyNot($rate)
            ->when($maximumWeight !== null, function (Builder $query) use ($maximumWeight): void {
                $query->where('minimum_weight', '<', $maximumWeight);
            })
            ->where(function (Builder $query) use ($minimumWeight): void {
                $query->whereNull('maximum_weight')
                    ->orWhere('maximum_weight', '>', $minimumWeight);
            })
            ->exists();

        if ($overlappingRateExists) {
            throw CannotUpdatePlanRate::becauseItOverlapsAnExistingRate();
        }
    }

    private function maximumWeight(PlanRate $rate, UpdatePlanRateInput $input): null|float
    {
        if ($input->maximumWeight instanceof Optional) {
            return $rate->maximum_weight === null ? null : (float) $rate->maximum_weight;
        }

        return $input->maximumWeight === null ? null : (float) $input->maximumWeight;
    }
}
```
