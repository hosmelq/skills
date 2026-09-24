# Store Tests: Uniqueness Name With Required Fields

Pest POST store: Name duplicate/inactive reservation and reuse under required enum/transit payload; reuse asserts redirect without toast.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\ServicePlans\CreateServicePlan;
use App\Actions\ServicePlans\Inputs\CreateServicePlanInput;
use App\Enums\TransitTimeUnit;
use App\Enums\WeightUnit;
use App\Models\ServicePlan;
use App\Models\Team;

describe('store', function (): void {
    it('rejects a duplicate value in the same scope', function (): void {
        $servicePlan = ServicePlan::factory()->createOne([
            'name' => 'Air Freight',
        ]);

        signIn(team: $servicePlan->team);

        $response = post(route('teams.service-plans.store', [
            'team' => $servicePlan->team,
        ]), [
            'description' => 'Air Freight option',
            'estimated_transit_time_unit' => TransitTimeUnit::Days(),
            'maximum_estimated_transit_time' => 6,
            'minimum_estimated_transit_time' => 4,
            'name' => 'Air Freight',
            'weight_unit' => WeightUnit::Pounds(),
        ]);

        $response->assertRedirectBackWithErrors([
            'name' => 'The name has already been taken.',
        ]);
    });

    it('rejects a value reserved by an inactive record', function (): void {
        $servicePlan = ServicePlan::factory()->deactivated()->createOne([
            'name' => 'Air Freight',
        ]);

        signIn(team: $servicePlan->team);

        $response = post(route('teams.service-plans.store', [
            'team' => $servicePlan->team,
        ]), [
            'description' => 'Air Freight option',
            'estimated_transit_time_unit' => TransitTimeUnit::Days(),
            'maximum_estimated_transit_time' => 6,
            'minimum_estimated_transit_time' => 4,
            'name' => 'Air Freight',
            'weight_unit' => WeightUnit::Pounds(),
        ]);

        $response->assertRedirectBackWithErrors([
            'name' => 'The name has already been taken.',
        ]);
    });

    it('allows a value used in a different scope', function (): void {
        ServicePlan::factory()->createOne([
            'name' => 'Air Freight',
        ]);

        $team = Team::factory()->createOne();
        $createdServicePlan = ServicePlan::factory()
            ->for($team)
            ->createOne();

        signIn(team: $team);

        mock(CreateServicePlan::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Team $teamArgument, CreateServicePlanInput $input): bool => $teamArgument->is($team)
                && $input->name === 'Air Freight')
            ->andReturn($createdServicePlan);

        $response = post(route('teams.service-plans.store', [
            'team' => $team,
        ]), [
            'estimated_transit_time_unit' => TransitTimeUnit::Days(),
            'minimum_estimated_transit_time' => 4,
            'name' => 'Air Freight',
            'weight_unit' => WeightUnit::Pounds(),
        ]);

        $response->assertRedirectToRoute('teams.service-plans.show', [
            'team' => $team,
            'service_plan' => $createdServicePlan,
        ]);
    });

    it('allows a value used by a soft deleted record', function (): void {
        $servicePlan = ServicePlan::factory()
            ->trashed()
            ->createOne([
                'name' => 'Air Freight',
            ]);

        $replacement = ServicePlan::factory()
            ->for($servicePlan->team)
            ->createOne();

        signIn(team: $servicePlan->team);

        mock(CreateServicePlan::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Team $teamArgument, CreateServicePlanInput $input): bool => $teamArgument->is($servicePlan->team)
                && $input->name === 'Air Freight')
            ->andReturn($replacement);

        $response = post(route('teams.service-plans.store', [
            'team' => $servicePlan->team,
        ]), [
            'estimated_transit_time_unit' => TransitTimeUnit::Days(),
            'minimum_estimated_transit_time' => 4,
            'name' => 'Air Freight',
            'weight_unit' => WeightUnit::Pounds(),
        ]);

        $response->assertRedirectToRoute('teams.service-plans.show', [
            'team' => $servicePlan->team,
            'service_plan' => $replacement,
        ]);
    });
});
```
