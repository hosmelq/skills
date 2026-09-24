# Store Tests: Relations Service And Rule

Pest POST store: Selected service foreign/inactive/deleted; selected rule foreign/deleted or backed by inactive/deleted service. The two-row direct-owner/parent-owner dataset remains complete.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use Database\Factories\ServicePlanFactory;

describe('store', function (): void {
    it('rejects a newly assigned relation from another tenant: service_plan_id', function (): void {
        $team = Team::factory()->createOne();
        $servicePlan = ServicePlan::factory()->createOne();

        login(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'service_plan_id' => $servicePlan->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'service_plan_id' => 'The selected service plan id is invalid.',
        ]);
    });

    it('rejects a newly assigned relation from another tenant: plan_rule_id', function (bool $sameParentTeam): void {
        $team = Team::factory()->createOne();
        $servicePlan = ServicePlan::factory()
            ->when($sameParentTeam, fn (ServicePlanFactory $factory): ServicePlanFactory => $factory->recycle($team))
            ->createOne();
        $planRule = PlanRule::factory()
            ->for(Team::factory())
            ->for($servicePlan)
            ->createOne();

        login(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'plan_rule_id' => $planRule->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'plan_rule_id' =>
                'The selected plan rule id is invalid.',
        ]);
    })->with([
        'different parent team' => false,
        'same parent team' => true,
    ]);

    it('rejects a newly assigned inactive relation: service_plan_id', function (): void {
        $team = Team::factory()->createOne();
        $servicePlan = ServicePlan::factory()->deactivated()->recycle($team)->createOne();

        login(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'service_plan_id' => $servicePlan->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'service_plan_id' => 'The selected service plan id is invalid.',
        ]);
    });

    it('rejects a newly assigned soft deleted relation: service_plan_id', function (): void {
        $team = Team::factory()->createOne();
        $servicePlan = ServicePlan::factory()->trashed()->recycle($team)->createOne();

        login(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'service_plan_id' => $servicePlan->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'service_plan_id' => 'The selected service plan id is invalid.',
        ]);
    });

    it('rejects a newly assigned soft deleted relation: plan_rule_id', function (): void {
        $team = Team::factory()->createOne();
        $planRule = PlanRule::factory()->trashed()->recycle($team)->createOne();

        login(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'plan_rule_id' => $planRule->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'plan_rule_id' =>
                'The selected plan rule id is invalid.',
        ]);
    });

    it('rejects a newly assigned relation with an inactive parent', function (): void {
        $servicePlan = ServicePlan::factory()->deactivated()->createOne();
        $team = $servicePlan->team;
        $planRule = PlanRule::factory()->recycle($servicePlan)->createOne();

        login(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'plan_rule_id' => $planRule->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'plan_rule_id' =>
                'The selected plan rule id is invalid.',
        ]);
    });

    it('rejects a newly assigned relation with a soft deleted parent', function (): void {
        $servicePlan = ServicePlan::factory()->trashed()->createOne();
        $team = $servicePlan->team;
        $planRule = PlanRule::factory()->recycle($servicePlan)->createOne();

        login(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'plan_rule_id' => $planRule->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'plan_rule_id' =>
                'The selected plan rule id is invalid.',
        ]);
    });
});
```
