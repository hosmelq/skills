# Update Tests: Mapping Rule Rounding

Pest PATCH update: Rule update preserves the typed input and two stored-increment behaviors: explicit null when disabling rounding and retention when changing another field.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ServicePlans\Inputs\UpdatePlanRuleInput;
use App\Actions\ServicePlans\UpdatePlanRule;
use App\Enums\BillableWeightRoundingMode;
use App\Enums\CurrencyCode;
use App\Models\PlanRule;

describe('update', function (): void {
    it('updates the record', function (): void {
        $planRule = PlanRule::factory()->createOne();

        login(team: $planRule->servicePlan->team);

        mock(UpdatePlanRule::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (PlanRule $planRuleArgument, UpdatePlanRuleInput $input): bool => $planRuleArgument->is($planRule)
                && $input->currencyCode === CurrencyCode::CNY);

        $response = patch(route('teams.service-plans.plan-rules.update', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]), [
            'currency_code' => CurrencyCode::CNY->value,
        ]);

        $response->assertRedirectToRoute('teams.service-plans.plan-rules.show', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ])
            ->assertToast('Plan rule updated');
    });

    it('clears the rounding increment when rounding is disabled', function (): void {
        $planRule = PlanRule::factory()->roundUp()->createOne();

        login(team: $planRule->servicePlan->team);

        mock(UpdatePlanRule::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (PlanRule $planRuleArgument, UpdatePlanRuleInput $input): bool => $planRuleArgument->is($planRule)
                && $input->billableWeightRoundingIncrement === null
                && $input->billableWeightRoundingMode === BillableWeightRoundingMode::None);

        $response = patch(route('teams.service-plans.plan-rules.update', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]), [
            'billable_weight_rounding_mode' => BillableWeightRoundingMode::None->value,
        ]);

        $response->assertRedirectToRoute('teams.service-plans.plan-rules.show', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ])
            ->assertToast('Plan rule updated');
    });

    it('retains the stored increment while updating another field', function (): void {
        $planRule = PlanRule::factory()->roundUp()->createOne();

        login(team: $planRule->servicePlan->team);

        mock(UpdatePlanRule::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (PlanRule $planRuleArgument, UpdatePlanRuleInput $input): bool => $planRuleArgument->is($planRule)
                && $input->billableWeightRoundingIncrement === '1.0000'
                && $input->minimumBillableWeight === '2');

        $response = patch(route('teams.service-plans.plan-rules.update', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]), [
            'minimum_billable_weight' => 2,
        ]);

        $response->assertRedirectToRoute('teams.service-plans.plan-rules.show', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ])
            ->assertToast('Plan rule updated');
    });
});
```
