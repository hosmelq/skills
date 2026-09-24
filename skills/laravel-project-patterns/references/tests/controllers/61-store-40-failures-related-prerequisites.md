# Store Tests: Failures Related Prerequisites

Pest POST store: Owner/assignment mismatch, rule without service, wrong rule parent and mismatched measurement unit; each field/message retained.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Exceptions\WorkOrders\MemberDoesNotOwnCabinet;
use App\Exceptions\WorkOrders\PlanRuleDoesNotBelongToServicePlan;
use App\Exceptions\WorkOrders\PlanRuleRequiresServicePlan;
use App\Exceptions\WorkOrders\WeightUnitDoesNotMatchServicePlan;
use App\Models\Team;

describe('store', function (): void {
    it('maps a relation ownership mismatch to validation', function (): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        mock(CreateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(MemberDoesNotOwnCabinet::becauseTheCabinetBelongsToAnotherMember());

        $response = post(route('teams.work-orders.store', $team));

        $response->assertRedirectBackWithErrors([
            'member_id' => 'The selected member must own the selected cabinet.',
        ]);
    });

    it('maps a missing prerequisite relation rejection to validation', function (): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        mock(CreateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(PlanRuleRequiresServicePlan::becauseNoneWasSelected());

        $response = post(route('teams.work-orders.store', $team));

        $response->assertRedirectBackWithErrors([
            'plan_rule_id' =>
                'Select a service plan before selecting a plan rule.',
        ]);
    });

    it('maps a dependent relation mismatch to validation', function (
    ): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        mock(CreateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(PlanRuleDoesNotBelongToServicePlan::becauseTheyDoNotMatch());

        $response = post(route('teams.work-orders.store', $team));

        $response->assertRedirectBackWithErrors([
            'plan_rule_id' =>
                'The selected plan rule does not belong to the selected service plan.',
        ]);
    });

    it('maps a mismatched weight unit rejection to validation', function (): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        mock(CreateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(
                WeightUnitDoesNotMatchServicePlan::becauseItDiffersFromTheServicePlan(),
            );

        $response = post(route('teams.work-orders.store', $team));

        $response->assertRedirectBackWithErrors([
            'weight_unit' => 'The work order weight unit must match the selected service plan.',
        ]);
    });
});
```
