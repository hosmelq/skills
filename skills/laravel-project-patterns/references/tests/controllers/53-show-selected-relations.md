# Show Tests: Default And Selected Relations

Complete GET show page examples expose a factory-selected default relation or an explicitly selected lateral relation. Assert the exact related public ID alongside the component and applicable route IDs and public child fields.

## Default Relation

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Member;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('shows the detail page with the default relation', function (): void {
        $member = Member::factory()
            ->withDefaultAddress()
            ->createOne();

        signIn(team: $member->team);

        $response = get(route('teams.members.show', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($member): void {
                $page->component('members/Show')
                    ->where('member.id', $member->public_id)
                    ->where('defaultAddress.id', $member->defaultAddress->public_id)
                    ->where('team.id', $member->team->public_id);
            });
    });
});
```

## Selected Relation

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Cabinet;
use App\Models\Member;
use App\Models\ServicePlan;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('shows the detail page with the selected relation', function (): void {
        $member = Member::factory()->createOne();
        $servicePlan = ServicePlan::factory()->for($member->team)->createOne();
        $cabinet = Cabinet::factory()
            ->for($member)
            ->for($member->team)
            ->for($servicePlan)
            ->createOne();

        signIn(team: $member->team);

        $response = get(route('teams.members.cabinets.show', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($member, $cabinet, $servicePlan): void {
                $page->component('members/cabinets/Show')
                    ->where('member.id', $member->public_id)
                    ->where('cabinet.id', $cabinet->public_id)
                    ->where('cabinet.code', $cabinet->code)
                    ->where('team.id', $member->team->public_id)
                    ->where('servicePlan.id', $servicePlan->public_id);
            });
    });
});
```
