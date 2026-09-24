# Destroy Tests: Live And Soft Deleted Dependencies

DELETE destroy translates mocked dependency rejections with live and soft-deleted child or related-record fixtures. Preserve separate real dependency fixtures, identity-matched mocked action exceptions and exact redirect-back errors; the mock does not prove dependency detection inside the action.

Keep live and trashed variants separate. Related-record examples create a valid intermediary in the same tenant.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\ServicePlans\DeleteServicePlan;
use App\Exceptions\CannotDeleteServicePlanInUse;
use App\Models\Cabinet;
use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('destroy', function (): void {
    it('maps a child dependency rejection to validation', function (): void {
        $planRule = PlanRule::factory()->createOne();

        login(team: $planRule->servicePlan->team);

        mock(DeleteServicePlan::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument): bool => $servicePlanArgument->is($planRule->servicePlan))
            ->andThrow(new CannotDeleteServicePlanInUse());

        $response = delete(route('teams.service-plans.destroy', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
        ]));

        $response->assertRedirectBackWithErrors([
            'service_plan' => 'This service plan has dependent configuration or operational references and cannot be deleted.',
        ]);
    });

    it('maps a soft deleted child dependency rejection to validation', function (): void {
        $planRule = PlanRule::factory()->trashed()->createOne();

        login(team: $planRule->servicePlan->team);

        mock(DeleteServicePlan::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument): bool => $servicePlanArgument->is($planRule->servicePlan))
            ->andThrow(new CannotDeleteServicePlanInUse());

        $response = delete(route('teams.service-plans.destroy', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
        ]));

        $response->assertRedirectBackWithErrors([
            'service_plan' => 'This service plan has dependent configuration or operational references and cannot be deleted.',
        ]);
    });

    it('maps a related dependency rejection to validation', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();
        Cabinet::factory()
            ->recycle($servicePlan->team)
            ->for($servicePlan)
            ->createOne();

        login(team: $servicePlan->team);

        mock(DeleteServicePlan::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument): bool => $servicePlanArgument->is($servicePlan))
            ->andThrow(new CannotDeleteServicePlanInUse());

        $response = delete(route('teams.service-plans.destroy', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]));

        $response->assertRedirectBackWithErrors([
            'service_plan' => 'This service plan has dependent configuration or operational references and cannot be deleted.',
        ]);
    });

    it('maps a soft deleted related dependency rejection to validation', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();
        Cabinet::factory()
            ->trashed()
            ->recycle($servicePlan->team)
            ->for($servicePlan)
            ->createOne();

        login(team: $servicePlan->team);

        mock(DeleteServicePlan::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument): bool => $servicePlanArgument->is($servicePlan))
            ->andThrow(new CannotDeleteServicePlanInUse());

        $response = delete(route('teams.service-plans.destroy', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]));

        $response->assertRedirectBackWithErrors([
            'service_plan' => 'This service plan has dependent configuration or operational references and cannot be deleted.',
        ]);
    });
});
```
