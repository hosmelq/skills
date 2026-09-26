# Actions: Create a Scoped Assignment

Resolve a related record within the owner tenant, lock and check it, generate a code, then create an assignment. Translate only the specified unique-index violation and rethrow other failures.

The synthetic index name must match the target schema. Laravel 13 exposes `UniqueConstraintViolationException::$index`. Use a driver that reports the named index; SQLite reports columns instead. The code generator returns a candidate, not a reservation.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Cabinets;

use App\Actions\Cabinets\Inputs\CreateCabinetInput;
use App\Actions\GenerateCabinetCode;
use App\Actions\ServicePlans\EnsureActiveServicePlan;
use App\Exceptions\CannotCreateCabinet;
use App\Models\Cabinet;
use App\Models\Member;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class CreateCabinet
{
    public function __construct(
        private readonly EnsureActiveServicePlan $ensureActiveServicePlan,
        private readonly GenerateCabinetCode $generateCabinetCode,
    ) {
    }

    public function handle(Member $member, CreateCabinetInput $input): Cabinet
    {
        return DB::transaction(function () use ($member, $input): Cabinet {
            $attributes = $input->transform();
            /** @var int $servicePlanId */
            $servicePlanId = $attributes['service_plan_id'];

            $team = $member->team()->firstOrFail();

            $servicePlan = $team->servicePlans()
                ->whereKey($servicePlanId)
                ->firstOrFail();

            $activeServicePlan = $this->ensureActiveServicePlan->handle($servicePlan);

            $attributes['service_plan_id'] = $activeServicePlan->id;
            $code = $this->generateCabinetCode->handle($team);

            try {
                return $member->cabinets()->create([
                    ...$attributes,
                    'code' => $code,
                    'normalized_code' => Cabinet::normalizeCode($code),
                    'team_id' => $team->id,
                ]);
            } catch (UniqueConstraintViolationException $exception) {
                if ($exception->index === 'cabinets_active_member_service_plan_unique') {
                    throw CannotCreateCabinet::becauseServicePlanIsAlreadyAssigned();
                }

                throw $exception;
            }
        });
    }
}
```
