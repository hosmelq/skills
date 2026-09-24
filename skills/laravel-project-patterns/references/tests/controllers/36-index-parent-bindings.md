# Index Tests: Scoped Parent Bindings

Complete GET index 404 examples for foreign and soft deleted route parents, including two-level ancestor chains and an intermediate parent from the wrong ancestor in the same tenant.

## One Parent

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Member;
use App\Models\Team;

describe('index', function (): void {
    it('returns not found when the parent belongs to another tenant', function (): void {
        $relatedTeam = Team::factory()->createOne();
        $unrelatedMember = Member::factory()->createOne();

        login(team: $relatedTeam);

        $response = get(route('teams.members.addresses.index', [
            'team' => $relatedTeam,
            'member' => $unrelatedMember,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent is soft deleted', function (): void {
        $member = Member::factory()->trashed()->createOne();

        login(team: $member->team);

        $response = get(route('teams.members.addresses.index', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertNotFound();
    });
});
```

## Two Parents

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;

describe('index', function (): void {
    it('returns not found when the ancestor belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $planRule = PlanRule::factory()->createOne();

        login(team: $team);

        $response = get(route('teams.service-plans.plan-rules.rates.index', [
            'team' => $team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the ancestor is soft deleted', function (): void {
        $servicePlan = ServicePlan::factory()->trashed()->createOne();
        $planRule = PlanRule::factory()->recycle($servicePlan)->createOne();

        login(team: $servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.index', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent belongs to another ancestor in the same tenant', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();
        $planRule = PlanRule::factory()->recycle($servicePlan->team)->createOne();

        login(team: $servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.index', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent belongs to another tenant', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();
        $unrelatedPlanRule = PlanRule::factory()->createOne();

        login(team: $servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.index', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $unrelatedPlanRule,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent is soft deleted', function (): void {
        $planRule = PlanRule::factory()->trashed()->createOne();

        login(team: $planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.index', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertNotFound();
    });
});
```
