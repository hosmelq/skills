# Destroy Tests: Scoped Parent And Ancestor Binding

DELETE destroy ancestors and parents that are foreign, soft deleted or under the wrong same-tenant ancestor return 404. Includes one-parent and two-parent routes, valid descendant fixtures and a mutation-action guard for the foreign intermediate parent.

Authorize the URL tenant. Change only the targeted route level and retain valid descendants. Target-record mismatches have separate examples.

## One Parent

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;

use App\Models\Member;
use App\Models\MemberAddress;
use App\Models\Team;

describe('destroy', function (): void {
    it('returns not found when the parent belongs to another tenant', function (): void {
        $address = MemberAddress::factory()->createOne();
        $team = Team::factory()->createOne();

        login(team: $team);

        $response = delete(route('teams.members.addresses.destroy', [
            'team' => $team,
            'member' => $address->member,
            'address' => $address,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent is soft deleted', function (): void {
        $member = Member::factory()->trashed()->createOne();
        $address = MemberAddress::factory()->recycle($member)->createOne();

        login(team: $member->team);

        $response = delete(route('teams.members.addresses.destroy', [
            'team' => $member->team,
            'member' => $member,
            'address' => $address,
        ]));

        $response->assertNotFound();
    });
});
```

## Ancestor And Parent

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\ServicePlans\DeletePlanRate;
use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;

describe('destroy', function (): void {
    it('returns not found when the ancestor belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $rate = PlanRate::factory()->createOne();

        login(team: $team);

        $response = delete(route('teams.service-plans.plan-rules.rates.destroy', [
            'team' => $team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the ancestor is soft deleted', function (): void {
        $servicePlan = ServicePlan::factory()->trashed()->createOne();
        $rate = PlanRate::factory()->recycle($servicePlan)->createOne();

        login(team: $servicePlan->team);

        $response = delete(route('teams.service-plans.plan-rules.rates.destroy', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent belongs to another ancestor in the same tenant', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();
        $rate = PlanRate::factory()->recycle($servicePlan->team)->createOne();

        login(team: $servicePlan->team);

        $response = delete(route('teams.service-plans.plan-rules.rates.destroy', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent belongs to another tenant', function (): void {
        $rate = PlanRate::factory()->createOne();
        $unrelatedPlanRule = PlanRule::factory()->createOne();

        login(team: $rate->planRule->servicePlan->team);

        mock(DeletePlanRate::class)
            ->shouldNotReceive('handle');

        $response = delete(route('teams.service-plans.plan-rules.rates.destroy', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $unrelatedPlanRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent is soft deleted', function (): void {
        $planRule = PlanRule::factory()->trashed()->createOne();
        $rate = PlanRate::factory()->recycle($planRule)->createOne();

        login(team: $planRule->servicePlan->team);

        $response = delete(route('teams.service-plans.plan-rules.rates.destroy', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });
});
```
