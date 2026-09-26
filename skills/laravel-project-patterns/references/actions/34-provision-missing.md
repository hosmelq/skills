# Actions: Provision Eligible Missing Records

Gate on enabled and approved state, iterate active parents by ID and skip existing assignments. After a mapped creation conflict, re-query and suppress only if the assignment now exists.

Nested action transactions and the collaborator call order are intentional. This recovery branch handles an observed conflict; a synchronous stub does not prove concurrent execution.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Cabinets;

use App\Actions\Cabinets\Inputs\CreateCabinetInput;
use App\Enums\EnrollmentStatus;
use App\Exceptions\CannotCreateCabinet;
use App\Models\Cabinet;
use App\Models\Enrollment;
use App\Models\Member;
use App\Models\ServicePlan;
use Illuminate\Support\Facades\DB;

class ProvisionMemberCabinets
{
    public function __construct(private readonly CreateCabinet $createCabinet)
    {
    }

    public function handle(Enrollment $enrollment): void
    {
        if (
            ! $enrollment->team->cabinets_enabled
            || $enrollment->status !== EnrollmentStatus::Approved
        ) {
            return;
        }

        DB::transaction(function () use ($enrollment): void {
            $enrollment->team
                ->servicePlans()
                ->active()
                ->eachById(function (ServicePlan $servicePlan) use ($enrollment): void {
                    $this->provisionCabinet($enrollment->member, $servicePlan);
                });
        });
    }

    private function provisionCabinet(Member $member, ServicePlan $servicePlan): void
    {
        if ($member->cabinets()->where('service_plan_id', $servicePlan->id)->exists()) {
            return;
        }

        try {
            $this->createCabinet->handle(
                $member,
                CreateCabinetInput::from([
                    'service_plan_id' => $servicePlan->id,
                ]),
            );
        } catch (CannotCreateCabinet $exception) {
            $cabinetExists = Cabinet::query()
                ->where('member_id', $member->id)
                ->where('service_plan_id', $servicePlan->id)
                ->exists();

            throw_unless($cabinetExists, $exception);
        }
    }
}
```
