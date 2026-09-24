# Action Tests: Dispatch After Creation

Integration tests for dispatching a provisioning job after creating an eligible parent. Bus fakes assert dispatch or no dispatch according to a tenant setting; they do not execute the job.

```php
<?php

declare(strict_types=1);

use App\Actions\ServicePlans\CreateServicePlan;
use App\Actions\ServicePlans\Inputs\CreateServicePlanInput;
use App\Enums\TransitTimeUnit;
use App\Enums\WeightUnit;
use App\Jobs\ProvisionTeamCabinets;
use App\Models\Team;
use Illuminate\Support\Facades\Bus;

it('does not dispatch assignment provisioning when assignment self-service is disabled', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => false]);

    Bus::fake();

    resolve(CreateServicePlan::class)->handle(
        $team,
        CreateServicePlanInput::from([
            'estimated_transit_time_unit' => TransitTimeUnit::Days(),
            'minimum_estimated_transit_time' => 4,
            'name' => 'Standard plan',
            'weight_unit' => WeightUnit::Pounds(),
        ]),
    );

    Bus::assertNotDispatched(ProvisionTeamCabinets::class);
});

it('dispatches assignment provisioning when assignment self-service is enabled', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => true]);

    Bus::fake();

    resolve(CreateServicePlan::class)->handle(
        $team,
        CreateServicePlanInput::from([
            'estimated_transit_time_unit' => TransitTimeUnit::Days(),
            'minimum_estimated_transit_time' => 4,
            'name' => 'Standard plan',
            'weight_unit' => WeightUnit::Pounds(),
        ]),
    );

    Bus::assertDispatched(
        ProvisionTeamCabinets::class,
        fn (ProvisionTeamCabinets $job): bool => $job->team->is($team),
    );
});
```
