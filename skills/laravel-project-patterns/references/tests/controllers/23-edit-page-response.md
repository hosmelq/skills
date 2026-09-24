# Edit Tests: Direct Page And Authentication

Complete direct-record GET edit block: login redirect, unrelated tenant 403, foreign or trashed target 404, then HTTP 200 with component and public IDs. Separate nested authentication examples retain every valid route parameter at one and two parent levels.

Use the same canonical names across controllers. Nested authorization examples replace the direct ones when assembling a nested block.

## Direct Record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Member;
use App\Models\Team;
use Inertia\Testing\AssertableInertia;

describe('edit', function (): void {
    it('requires authentication', function (): void {
        $member = Member::factory()->createOne();

        $response = get(route('teams.members.edit', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('prevents viewing from an unrelated tenant', function (): void {
        $unrelatedMember = Member::factory()->createOne();

        login();

        $response = get(route('teams.members.edit', [
            'team' => $unrelatedMember->team,
            'member' => $unrelatedMember,
        ]));

        $response->assertForbidden();
    });

    it('returns not found when the record belongs to another tenant', function (): void {
        $relatedTeam = Team::factory()->createOne();
        $unrelatedMember = Member::factory()->createOne();

        login(team: $relatedTeam);

        $response = get(route('teams.members.edit', [
            'team' => $relatedTeam,
            'member' => $unrelatedMember,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record is soft deleted', function (): void {
        $member = Member::factory()->trashed()->createOne();

        login(team: $member->team);

        $response = get(route('teams.members.edit', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertNotFound();
    });

    it('shows the edit page', function (): void {
        $member = Member::factory()->createOne();

        login(team: $member->team);

        $response = get(route('teams.members.edit', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($member): void {
                $page->component('members/Edit')
                    ->where('member.id', $member->public_id)
                    ->where('team.id', $member->team->public_id);
            });
    });
});
```

## One Parent Authentication

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\MemberAddress;

describe('edit', function (): void {
    it('requires authentication', function (): void {
        $address = MemberAddress::factory()->createOne();

        $response = get(route('teams.members.addresses.edit', [
            'team' => $address->member->team,
            'member' => $address->member,
            'address' => $address,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('prevents viewing from an unrelated tenant', function (): void {
        $address = MemberAddress::factory()->createOne();

        login();

        $response = get(route('teams.members.addresses.edit', [
            'team' => $address->member->team,
            'member' => $address->member,
            'address' => $address,
        ]));

        $response->assertForbidden();
    });
});
```

## Two Parent Authentication

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRate;

describe('edit', function (): void {
    it('requires authentication', function (): void {
        $rate = PlanRate::factory()->createOne();

        $response = get(route('teams.service-plans.plan-rules.rates.edit', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('prevents viewing from an unrelated tenant', function (): void {
        $rate = PlanRate::factory()->createOne();

        login();

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
