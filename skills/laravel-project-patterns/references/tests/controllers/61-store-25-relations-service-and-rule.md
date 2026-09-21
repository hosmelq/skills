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
    it('rejects a service plan from another tenant', function (): void {
        $team = Team::factory()->createOne();
        $servicePlan = ServicePlan::factory()->createOne();

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'service_plan_id' => $servicePlan->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'service_plan_id' => 'The selected service plan id is invalid.',
        ]);
    });

    it('rejects an inactive service plan', function (): void {
        $team = Team::factory()->createOne();
        $servicePlan = ServicePlan::factory()->deactivated()->for($team)->createOne();

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'service_plan_id' => $servicePlan->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'service_plan_id' => 'The selected service plan id is invalid.',
        ]);
    });

    it('rejects a soft deleted service plan', function (): void {
        $team = Team::factory()->createOne();
        $servicePlan = ServicePlan::factory()->trashed()->for($team)->createOne();

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'service_plan_id' => $servicePlan->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'service_plan_id' => 'The selected service plan id is invalid.',
        ]);
    });

    it('rejects a soft deleted plan rule', function (): void {
        $team = Team::factory()->createOne();
        $planRule = PlanRule::factory()
            ->trashed()
            ->for(ServicePlan::factory()->for($team))
            ->createOne();

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'plan_rule_id' => $planRule->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'plan_rule_id' =>
                'The selected plan rule id is invalid.',
        ]);
    });

    it('rejects a plan rule from another tenant', function (bool $sameParentTeam): void {
        $team = Team::factory()->createOne();
        $servicePlan = ServicePlan::factory()
            ->when($sameParentTeam, fn (ServicePlanFactory $factory): ServicePlanFactory => $factory->for($team))
            ->createOne();
        $planRule = PlanRule::factory()
            ->for(Team::factory())
            ->for($servicePlan)
            ->createOne();

        signIn(team: $team);

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

    it('rejects a plan rule for an inactive service plan', function (): void {
        $servicePlan = ServicePlan::factory()->deactivated()->createOne();
        $team = $servicePlan->team;
        $planRule = PlanRule::factory()
            ->for($servicePlan)
            ->createOne();

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'plan_rule_id' => $planRule->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'plan_rule_id' =>
                'The selected plan rule id is invalid.',
        ]);
    });

    it('rejects a plan rule for a soft deleted service plan', function (): void {
        $servicePlan = ServicePlan::factory()->trashed()->createOne();
        $team = $servicePlan->team;
        $planRule = PlanRule::factory()
            ->for($servicePlan)
            ->createOne();

        signIn(team: $team);

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
