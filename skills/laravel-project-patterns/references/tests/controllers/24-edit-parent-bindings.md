# Edit Tests: Scoped Parent And Ancestor Binding

GET edit returns 404 for foreign or soft-deleted route parents and ancestors, including a parent under another ancestor in the same tenant. Complete child and grandchild fixtures isolate the failed route level while the URL tenant is authorized.

Keep valid descendants and change only the targeted route level. These cases precede target-record binding failures.

## One Parent

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Member;
use App\Models\MemberAddress;
use App\Models\Team;

describe('edit', function (): void {
    it('returns not found when the parent belongs to another tenant', function (): void {
        $address = MemberAddress::factory()->createOne();
        $team = Team::factory()->createOne();

        login(team: $team);

        $response = get(route('teams.members.addresses.edit', [
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

        $response = get(route('teams.members.addresses.edit', [
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

use function Pest\Laravel\get;

use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;

describe('edit', function (): void {
    it('returns not found when the ancestor belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $rate = PlanRate::factory()->createOne();

        login(team: $team);

        $response = get(route('teams.service-plans.plan-rules.rates.edit', [
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

        $response = get(route('teams.service-plans.plan-rules.rates.edit', [
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

        $response = get(route('teams.service-plans.plan-rules.rates.edit', [
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

        $response = get(route('teams.service-plans.plan-rules.rates.edit', [
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

        $response = get(route('teams.service-plans.plan-rules.rates.edit', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });
});
```
