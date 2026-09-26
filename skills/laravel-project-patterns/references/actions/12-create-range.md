# Actions: Create a Non-Overlapping Range

Create a half-open range under an active ancestor: omitted/null upper bound is open-ended, reject a second open-ended row and overlapping intervals before relation creation.

Bounds and numeric validity come from the inspected validation contract. Adjacent bounds are allowed. These queries alone do not guarantee exclusion under concurrent writes.

```php
<?php

declare(strict_types=1);

namespace App\Actions\ServicePlans;

use App\Actions\ServicePlans\Inputs\CreatePlanRateInput;
use App\Exceptions\CannotCreatePlanRate;
use App\Models\PlanRate;
use App\Models\PlanRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;

class CreatePlanRate
{
    public function __construct(private readonly EnsureActiveServicePlan $ensureActiveServicePlan)
    {
    }

    public function handle(PlanRule $planRule, CreatePlanRateInput $input): PlanRate
    {
        return DB::transaction(function () use ($input, $planRule): PlanRate {
            $this->ensureActiveServicePlan->handle($planRule->servicePlan()->firstOrFail());

            $this->ensureRangeIsAvailable($planRule, $input);

            return $planRule->rates()->create($input->transform());
        });
    }

    private function ensureRangeIsAvailable(PlanRule $planRule, CreatePlanRateInput $input): void
    {
        $minimumWeight = (float) $input->minimumWeight;
        $maximumWeight = $input->maximumWeight instanceof Optional ? null : $input->maximumWeight;

        if ($maximumWeight === null && $planRule->rates()->whereNull('maximum_weight')->exists()) {
            throw CannotCreatePlanRate::becauseItHasAnOpenEndedRate();
        }

        $overlappingRateExists = $planRule->rates()
            ->when($maximumWeight !== null, function (Builder $query) use ($maximumWeight): void {
                $query->where('minimum_weight', '<', (float) $maximumWeight);
            })
            ->where(function (Builder $query) use ($minimumWeight): void {
                $query->whereNull('maximum_weight')
                    ->orWhere('maximum_weight', '>', $minimumWeight);
            })
            ->exists();

        if ($overlappingRateExists) {
            throw CannotCreatePlanRate::becauseItOverlapsAnExistingRate();
        }
    }
}
```
