# Index Tests: Page And Collection Props

Successful GET index examples for component and tenant IDs, nested ancestor IDs, a known first row, exact child count, public row fields and enum option arrays. Select the matching response contract and keep its assertions.

Use `shows the index page` across controllers. These are alternative page contracts; a first-row assertion does not prove the collection count.

## Direct Row

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Member;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('shows the index page', function (): void {
        $member = Member::factory()->createOne();

        signIn(team: $member->team);

        $response = get(route('teams.members.index', [
            'team' => $member->team,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($member): void {
                $page->component('members/Index')
                    ->where('members.data.0.id', $member->public_id)
                    ->where('team.id', $member->team->public_id);
            });
    });
});
```

## Nested Row

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRule;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('shows the index page', function (): void {
        $planRule = PlanRule::factory()->createOne();

        signIn(team: $planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.index', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($planRule): void {
                $page->component('service-plans/plan-rules/Index')
                    ->where('team.id', $planRule->servicePlan->team->public_id)
                    ->where('planRules.data.0.id', $planRule->public_id)
                    ->where('servicePlan.id', $planRule->servicePlan->public_id);
            });
    });
});
```

## Two Parent Row

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRate;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('shows the index page', function (): void {
        $rate = PlanRate::factory()->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.index', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($rate): void {
                $page->component('service-plans/rates/Index')
                    ->where('team.id', $rate->planRule->servicePlan->team->public_id)
                    ->where('planRule.id', $rate->planRule->public_id)
                    ->where('rates.data.0.id', $rate->public_id)
                    ->where('servicePlan.id', $rate->planRule->servicePlan->public_id);
            });
    });
});
```

## Child Count

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Member;
use App\Models\MemberAddress;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('shows the index page', function (): void {
        $member = Member::factory()->createOne();

        MemberAddress::factory()->recycle($member)->count(2)->create();

        signIn(team: $member->team);

        $response = get(route('teams.members.addresses.index', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($member): void {
                $page->component('members/addresses/Index')
                    ->where('member.id', $member->public_id)
                    ->where('team.id', $member->team->public_id)
                    ->has('addresses.data', 2);
            });
    });
});
```

## Public Row Fields

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Cabinet;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('shows the index page', function (): void {
        $cabinet = Cabinet::factory()->createOne();

        signIn(team: $cabinet->member->team);

        $response = get(route('teams.members.cabinets.index', [
            'team' => $cabinet->member->team,
            'member' => $cabinet->member,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($cabinet): void {
                $page->component('members/cabinets/Index')
                    ->where('member.id', $cabinet->member->public_id)
                    ->where('cabinets.data.0.id', $cabinet->public_id)
                    ->where('cabinets.data.0.code', $cabinet->code)
                    ->where('cabinets.data.0.label', $cabinet->label)
                    ->where('team.id', $cabinet->member->team->public_id);
            });
    });
});
```

## Enum Options

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\CountryCode;
use App\Enums\FacilityType;
use App\Models\Facility;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('shows the index page', function (): void {
        $facility = Facility::factory()->createOne();

        signIn(team: $facility->team);

        $response = get(route('teams.facilities.index', [
            'team' => $facility->team,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($facility): void {
                $page->component('facilities/Index')
                    ->where('countryCodes', CountryCode::options())
                    ->where('facilityTypes', FacilityType::options())
                    ->where('team.id', $facility->team->public_id)
                    ->where('facilities.data.0.id', $facility->public_id);
            });
    });
});
```
