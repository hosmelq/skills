# Edit Tests: Inactive Record Parent And Ancestor

GET edit policy 403 for an inactive direct or nested target, inactive immediate parent, or inactive ancestor. These examples use real restricted fixtures; they are distinct from trashed binding 404 and final-state forms that remain viewable with HTTP 200.

Apply only guards present in the inspected policy. Inactive selected relations and options have separate viewable-page contracts.

## Direct Target

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Facility;

describe('edit', function (): void {
    it('prevents viewing when the record is inactive', function (): void {
        $facility = Facility::factory()->deactivated()->createOne();

        signIn(team: $facility->team);

        $response = get(route('teams.facilities.edit', [
            'team' => $facility->team,
            'facility' => $facility,
        ]));

        $response->assertForbidden();
    });
});
```

## Nested Target

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Cabinet;

describe('edit', function (): void {
    it('prevents viewing when the record is inactive', function (): void {
        $cabinet = Cabinet::factory()->deactivated()->createOne();

        signIn(team: $cabinet->member->team);

        $response = get(route('teams.members.cabinets.edit', [
            'team' => $cabinet->member->team,
            'member' => $cabinet->member,
            'cabinet' => $cabinet,
        ]));

        $response->assertForbidden();
    });
});
```

## Parent

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('edit', function (): void {
    it('prevents viewing when the parent is inactive', function (): void {
        $planRule = PlanRule::factory()
            ->for(ServicePlan::factory()->deactivated())
            ->createOne();

        signIn(team: $planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.edit', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertForbidden();
    });
});
```

## Ancestor

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('edit', function (): void {
    it('prevents viewing when the ancestor is inactive', function (): void {
        $planRule = PlanRule::factory()
            ->for(ServicePlan::factory()->deactivated())
            ->createOne();
        $rate = PlanRate::factory()
            ->for($planRule, 'planRule')
            ->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.edit', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertForbidden();
    });
});
```
