# Edit Tests: Target Binding And Tenant Integrity

GET edit target failures: another same-tenant parent, another branch under the same ancestor, another same-tenant ancestor, another tenant, or a trashed record. Separate policy fixtures keep the correct parent foreign key but give the target a conflicting tenant ID.

Authorize the URL tenant for every 404 case. Preserve distinct country values for sibling rules where required by uniqueness. The correct-parent/foreign-tenant fixture tests policy integrity independently of scoped binding.

## Child Target

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Cabinet;
use App\Models\Member;
use App\Models\Team;

describe('edit', function (): void {
    it('returns not found when the record belongs to another parent in the same tenant', function (): void {
        $member = Member::factory()->createOne();
        $cabinet = Cabinet::factory()->recycle($member->team)->createOne();

        login(team: $member->team);

        $response = get(route('teams.members.cabinets.edit', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record belongs to another tenant', function (): void {
        $member = Member::factory()->createOne();
        $cabinet = Cabinet::factory()->createOne();

        login(team: $member->team);

        $response = get(route('teams.members.cabinets.edit', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record is soft deleted', function (): void {
        $cabinet = Cabinet::factory()->trashed()->createOne();

        login(team: $cabinet->member->team);

        $response = get(route('teams.members.cabinets.edit', [
            'team' => $cabinet->member->team,
            'member' => $cabinet->member,
            'cabinet' => $cabinet,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record tenant does not match its parent tenant', function (): void {
        $member = Member::factory()->createOne();
        $otherTeam = Team::factory()->createOne();
        $cabinet = Cabinet::factory()
            ->for($member)
            ->for($otherTeam)
            ->createOne();

        login(team: $member->team);

        $response = get(route('teams.members.cabinets.edit', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ]));

        $response->assertNotFound();
    });
});
```

## Grandchild Target

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\CountryCode;
use App\Models\PlanRate;
use App\Models\PlanRule;

describe('edit', function (): void {
    it('returns not found when the record belongs to another parent under the same ancestor', function (): void {
        $planRule = PlanRule::factory()
            ->forCountry(CountryCode::Canada)
            ->createOne();
        $rate = PlanRate::factory()->recycle($planRule)->createOne();
        $unrelatedPlanRule = PlanRule::factory()
            ->recycle($planRule->servicePlan)
            ->forCountry(CountryCode::Japan)
            ->createOne();
        $unrelatedRate = PlanRate::factory()->recycle($unrelatedPlanRule)->createOne();

        login(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.edit', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $unrelatedRate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record belongs to another ancestor in the same tenant', function (): void {
        $rate = PlanRate::factory()->createOne();
        $unrelatedRate = PlanRate::factory()->recycle($rate->planRule->servicePlan->team)->createOne();

        login(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.edit', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $unrelatedRate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record belongs to another tenant', function (): void {
        $planRule = PlanRule::factory()->createOne();
        $unrelatedRate = PlanRate::factory()->createOne();

        login(team: $planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.edit', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
            'rate' => $unrelatedRate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record is soft deleted', function (): void {
        $rate = PlanRate::factory()->trashed()->createOne();

        login(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.edit', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });
});
```
