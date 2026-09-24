# Index Tests: Authentication And Tenant Access

GET index authentication and unrelated-tenant 403 examples for direct, one-parent and two-parent collection routes. Keep every valid URL ancestor; adapt the route depth to the inspected endpoint.

## Direct Collection

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Member;
use App\Models\Team;

describe('index', function (): void {
    it('requires authentication', function (): void {
        $team = Team::factory()->createOne();

        $response = get(route('teams.members.index', [
            'team' => $team,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('prevents listing from an unrelated tenant', function (): void {
        $unrelatedMember = Member::factory()->createOne();

        login();

        $response = get(route('teams.members.index', [
            'team' => $unrelatedMember->team,
        ]));

        $response->assertForbidden();
    });
});
```

## One Parent

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Member;

describe('index', function (): void {
    it('requires authentication', function (): void {
        $member = Member::factory()->createOne();

        $response = get(route('teams.members.addresses.index', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('prevents listing from an unrelated tenant', function (): void {
        $member = Member::factory()->createOne();

        login();

        $response = get(route('teams.members.addresses.index', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertForbidden();
    });
});
```

## Two Parents

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRule;

describe('index', function (): void {
    it('requires authentication', function (): void {
        $planRule = PlanRule::factory()->createOne();

        $response = get(route('teams.service-plans.plan-rules.rates.index', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('prevents listing from an unrelated tenant', function (): void {
        $planRule = PlanRule::factory()->createOne();

        login();

        $response = get(route('teams.service-plans.plan-rules.rates.index', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertForbidden();
    });
});
```
