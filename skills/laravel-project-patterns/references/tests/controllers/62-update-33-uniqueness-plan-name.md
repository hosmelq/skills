# Update Tests: Uniqueness Plan Name

Pest PATCH update: Plan-name uniqueness keeps active and inactive conflicts distinct, with separate current/other-tenant/deleted acceptance fixtures and action mappings.

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
    it('rejects a duplicate value in the same scope', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();

        ServicePlan::factory()
            ->recycle($servicePlan->team)
            ->createOne(['name' => 'Air Freight']);

        login(team: $servicePlan->team);

        $response = patch(route('teams.service-plans.update', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), [
            'name' => 'Air Freight',
        ]);

        $response->assertRedirectBackWithErrors([
            'name' => 'The name has already been taken.',
        ]);
    });

    it('rejects a value reserved by an inactive record', function (): void {
        $deactivatedServicePlan = ServicePlan::factory()->deactivated()->createOne([
            'name' => 'Air Freight',
        ]);
        $servicePlan = ServicePlan::factory()->recycle($deactivatedServicePlan->team)->createOne();

        login(team: $servicePlan->team);

        $response = patch(route('teams.service-plans.update', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), [
            'name' => 'Air Freight',
        ]);

        $response->assertRedirectBackWithErrors([
            'name' => 'The name has already been taken.',
        ]);
    });

    it('allows a value used in a different scope', function (): void {
        ServicePlan::factory()->createOne([
            'name' => 'Air Freight',
        ]);

        $servicePlan = ServicePlan::factory()->createOne();

        login(team: $servicePlan->team);

        mock(UpdateServicePlan::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument, UpdateServicePlanInput $input): bool => $servicePlanArgument->is($servicePlan)
                && $input->name === 'Air Freight');

        $response = patch(route('teams.service-plans.update', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), [
            'name' => 'Air Freight',
        ]);

        $response->assertRedirectToRoute('teams.service-plans.show', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]);
    });

    it('allows the current value', function (): void {
        $servicePlan = ServicePlan::factory()->createOne([
            'name' => 'Air Freight',
        ]);

        login(team: $servicePlan->team);

        mock(UpdateServicePlan::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument, UpdateServicePlanInput $input): bool => $servicePlanArgument->is($servicePlan)
                && $input->name === 'Air Freight');

        $response = patch(route('teams.service-plans.update', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), [
            'name' => 'Air Freight',
        ]);

        $response->assertRedirectToRoute('teams.service-plans.show', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]);
    });

    it('allows a value used by a soft deleted record', function (): void {
        $softDeletedServicePlan = ServicePlan::factory()
            ->trashed()
            ->createOne([
                'name' => 'Air Freight',
            ]);
        $servicePlan = ServicePlan::factory()->recycle($softDeletedServicePlan->team)->createOne();

        login(team: $servicePlan->team);

        mock(UpdateServicePlan::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument, UpdateServicePlanInput $input): bool => $servicePlanArgument->is($servicePlan)
                && $input->name === 'Air Freight');

        $response = patch(route('teams.service-plans.update', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), [
            'name' => 'Air Freight',
        ]);

        $response->assertRedirectToRoute('teams.service-plans.show', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]);
    });
});
```
