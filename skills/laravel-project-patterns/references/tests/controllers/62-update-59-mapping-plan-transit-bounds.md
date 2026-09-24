# Update Tests: Mapping Plan Transit Bounds

Pest PATCH update: Plan update maps submitted fields, validates each changed transit bound against its stored counterpart, and accepts a lower-bound update when the stored upper bound is open ended.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ServicePlans\Inputs\UpdateServicePlanInput;
use App\Actions\ServicePlans\UpdateServicePlan;
use App\Models\ServicePlan;

describe('update', function (): void {
    it('validates an upper bound against the stored lower bound', function (): void {
        $servicePlan = ServicePlan::factory()->createOne([
            'maximum_estimated_transit_time' => 7,
            'minimum_estimated_transit_time' => 4,
        ]);

        login(team: $servicePlan->team);

        $response = patch(route('teams.service-plans.update', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), [
            'maximum_estimated_transit_time' => 3,
        ]);

        $response->assertRedirectBackWithErrors([
            'maximum_estimated_transit_time' => 'The maximum estimated transit time field must be greater than or equal to 4.',
        ]);
    });

    it('validates a lower bound against the stored upper bound', function (): void {
        $servicePlan = ServicePlan::factory()->createOne([
            'maximum_estimated_transit_time' => 5,
            'minimum_estimated_transit_time' => 3,
        ]);

        login(team: $servicePlan->team);

        $response = patch(route('teams.service-plans.update', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), [
            'minimum_estimated_transit_time' => 6,
        ]);

        $response->assertRedirectBackWithErrors([
            'minimum_estimated_transit_time' => 'The minimum estimated transit time field must be less than or equal to 5.',
        ]);
    });

    it('updates the record', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();

        login(team: $servicePlan->team);

        mock(UpdateServicePlan::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument, UpdateServicePlanInput $input): bool => $servicePlanArgument->is($servicePlan)
                && $input->name === 'Ocean Freight');

        $response = patch(route('teams.service-plans.update', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), [
            'name' => 'Ocean Freight',
        ]);

        $response->assertRedirectToRoute('teams.service-plans.show', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ])
            ->assertToast('Service plan updated');
    });

    it('allows a lower bound update with an open-ended stored upper bound', function (): void {
        $servicePlan = ServicePlan::factory()->createOne([
            'maximum_estimated_transit_time' => null,
            'minimum_estimated_transit_time' => 3,
        ]);

        login(team: $servicePlan->team);

        mock(UpdateServicePlan::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument, UpdateServicePlanInput $input): bool => $servicePlanArgument->is($servicePlan)
                && $input->minimumEstimatedTransitTime === 6);

        $response = patch(route('teams.service-plans.update', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), [
            'minimum_estimated_transit_time' => 6,
        ]);

        $response->assertRedirectToRoute('teams.service-plans.show', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ])
            ->assertToast('Service plan updated');
    });
});
```
