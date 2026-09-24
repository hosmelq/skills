# Index Tests: Tenant Collection Filters

Complete direct and nested GET index collection exclusions for another tenant. Exact count plus included and excluded IDs prove membership; preserve the parent chain of deeper fixtures.

## Direct Collection

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Member;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('excludes records from other tenants', function (): void {
        $member = Member::factory()->createOne();
        $unrelatedMember = Member::factory()->createOne();

        login(team: $member->team);

        $response = get(route('teams.members.index', [
            'team' => $member->team,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($member, $unrelatedMember): void {
                $page->component('members/Index')
                    ->has('members.data', 1, function (AssertableInertia $json) use ($member, $unrelatedMember): void {
                        $json
                            ->where('id', $member->public_id)
                            ->whereNot('id', $unrelatedMember->public_id)
                            ->etc();
                    })
                    ->where('team.id', $member->team->public_id);
            });
    });
});
```

## One Parent

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\MemberAddress;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('excludes records from other tenants', function (): void {
        $address = MemberAddress::factory()->createOne();
        $unrelatedAddress = MemberAddress::factory()->createOne();

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
                            ->where('id', $address->public_id)
                            ->whereNot('id', $unrelatedAddress->public_id)
                            ->etc();
                    });
            });
    });
});
```

## Two Parents

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRate;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('excludes records from other tenants', function (): void {
        $rate = PlanRate::factory()->createOne();
        $unrelatedRate = PlanRate::factory()->createOne();

        login(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.index', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($rate, $unrelatedRate): void {
                $page->component('service-plans/rates/Index')
                    ->has('rates.data', 1, function (AssertableJson $json) use ($rate, $unrelatedRate): void {
                        $json
                            ->where('id', $rate->public_id)
                            ->whereNot('id', $unrelatedRate->public_id)
                            ->etc();
                    });
            });
    });
});
```
