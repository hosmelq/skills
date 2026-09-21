# Store Tests: Failures Assignment

Pest POST store: Distinct action exceptions for inactive selected service and duplicate assignment; same mapped ID field but different guards/messages.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\Cabinets\CreateCabinet;
use App\Actions\Cabinets\Inputs\CreateCabinetInput;
use App\Exceptions\CannotCreateCabinet;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\Member;
use App\Models\ServicePlan;

describe('store', function (): void {
    it('rejects storing with an inactive selected service plan', function (): void {
        $member = Member::factory()->createOne();
        $servicePlan = ServicePlan::factory()
            ->deactivated()
            ->for($member->team)
            ->createOne();

        signIn(team: $member->team);

        mock(CreateCabinet::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                Member $memberArgument,
                CreateCabinetInput $input
            ): bool => $memberArgument->is($member)
                && $input->servicePlanId === $servicePlan->id)
            ->andThrow(CannotUseDeactivatedServicePlan::becauseItIsDeactivated());

        $response = post(route('teams.members.cabinets.store', [
            'team' => $member->team,
            'member' => $member,
        ]), [
            'service_plan_id' => $servicePlan->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'service_plan_id' => 'This service plan is deactivated.',
        ]);
    });

    it('rejects storing when the selected service plan is already assigned', function (): void {
        $member = Member::factory()->createOne();
        $servicePlan = ServicePlan::factory()->for($member->team)->createOne();

        signIn(team: $member->team);

        mock(CreateCabinet::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                Member $memberArgument,
                CreateCabinetInput $input
            ): bool => $memberArgument->is($member)
                && $input->servicePlanId === $servicePlan->id)
            ->andThrow(CannotCreateCabinet::becauseServicePlanIsAlreadyAssigned());

        $response = post(route('teams.members.cabinets.store', [
            'team' => $member->team,
            'member' => $member,
        ]), [
            'service_plan_id' => $servicePlan->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'service_plan_id' => 'This member already has a cabinet for the selected service plan.',
        ]);
    });
});
```
