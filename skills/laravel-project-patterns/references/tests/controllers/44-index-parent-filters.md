# Index Tests: Parent Collection Filters

GET index exclusions for another parent in the same tenant. The included and excluded rows share tenant ownership but differ at the tested parent edge; exact count and both IDs establish the result.

## Member Children

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\MemberAddress;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('excludes records from other parents in the same tenant', function (): void {
        $address = MemberAddress::factory()->createOne();
        $unrelatedAddress = MemberAddress::factory()->recycle($address->member->team)->createOne();

        login(team: $address->member->team);

        $response = get(route('teams.members.addresses.index', [
            'team' => $address->member->team,
            'member' => $address->member,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($address, $unrelatedAddress): void {
                $page->component('members/addresses/Index')
                    ->has('addresses.data', 1, function (AssertableInertia $json) use ($address, $unrelatedAddress): void {
                        $json
                            ->where('id', $address->sqid)
                            ->whereNot('id', $unrelatedAddress->sqid)
                            ->etc();
                    });
            });
    });
});
```

## Plan Children

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRule;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('excludes records from other parents in the same tenant', function (): void {
        $planRule = PlanRule::factory()->createOne();
        $unrelatedPlanRule = PlanRule::factory()->recycle($planRule->servicePlan->team)->createOne();

        login(team: $planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.index', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($planRule, $unrelatedPlanRule): void {
                $page->component('service-plans/plan-rules/Index')
                    ->has('planRules.data', 1, function (AssertableJson $json) use ($planRule, $unrelatedPlanRule): void {
                        $json
                            ->where('id', $planRule->sqid)
                            ->whereNot('id', $unrelatedPlanRule->sqid)
                            ->etc();
                    });
            });
    });
});
```
