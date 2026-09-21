# Show Tests: Scoped Parent Bindings

Complete GET show 404 cases for foreign or soft deleted parents and ancestors, plus an intermediate parent under another same-tenant ancestor. Keep live descendants and authorize the URL tenant to exercise the tested binding failures.

## One Parent

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Member;
use App\Models\MemberAddress;
use App\Models\Team;

describe('show', function (): void {
    it('returns not found when the parent belongs to another tenant', function (): void {
        $address = MemberAddress::factory()->createOne();
        $team = Team::factory()->createOne();

        signIn(team: $team);

        $response = get(route('teams.members.addresses.show', [
            'team' => $team,
            'member' => $address->member,
            'address' => $address,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent is soft deleted', function (): void {
        $member = Member::factory()->trashed()->createOne();
        $address = MemberAddress::factory()
            ->for($member)
            ->createOne();

        signIn(team: $member->team);

        $response = get(route('teams.members.addresses.show', [
            'team' => $member->team,
            'member' => $member,
            'address' => $address,
        ]));

        $response->assertNotFound();
    });
});
```

## Independent Parent Fixture

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('show', function (): void {
    it('returns not found when the parent belongs to another tenant', function (): void {
        $planRule = PlanRule::factory()->createOne();

        $unrelatedServicePlan = ServicePlan::factory()->createOne();

        signIn(team: $planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.show', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $unrelatedServicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertNotFound();
    });
});
```

## Inherited Ownership From Deleted Parent

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Cabinet;
use App\Models\Member;

describe('show', function (): void {
    it('returns not found when the parent is soft deleted', function (): void {
        $member = Member::factory()->trashed()->createOne();
        $cabinet = Cabinet::factory()->createOne([
            'member_id' => $member->id,
            'team_id' => $member->team_id,
        ]);

        signIn(team: $member->team);

        $response = get(route('teams.members.cabinets.show', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
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

use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;

describe('show', function (): void {
    it('returns not found when the ancestor belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $rate = PlanRate::factory()->createOne();

        signIn(team: $team);

        $response = get(route('teams.service-plans.plan-rules.rates.show', [
            'team' => $team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the ancestor is soft deleted', function (): void {
        $servicePlan = ServicePlan::factory()->trashed()->createOne();
        $planRule = PlanRule::factory()
            ->for($servicePlan)
            ->createOne();
        $rate = PlanRate::factory()
            ->for($planRule, 'planRule')
            ->createOne();

        signIn(team: $servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.show', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent belongs to another ancestor in the same tenant', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();
        $planRule = PlanRule::factory()
            ->recycle($servicePlan->team)
            ->createOne();
        $rate = PlanRate::factory()
            ->for($planRule, 'planRule')
            ->createOne();

        signIn(team: $servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.show', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent belongs to another tenant', function (): void {
        $rate = PlanRate::factory()->createOne();
        $unrelatedPlanRule = PlanRule::factory()->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.show', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $unrelatedPlanRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent is soft deleted', function (): void {
        $planRule = PlanRule::factory()->trashed()->createOne();
        $rate = PlanRate::factory()
            ->for($planRule, 'planRule')
            ->createOne();

        signIn(team: $planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.show', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });
});
```
