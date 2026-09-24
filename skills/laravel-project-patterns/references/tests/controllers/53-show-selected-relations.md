# Show Tests: Default And Selected Relations

GET show page examples expose a factory-selected default relation or an explicitly selected lateral relation. Assert the exact related public ID alongside the component and applicable route IDs and public child fields.

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

        login(team: $member->team);

        $response = get(route('teams.members.show', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($member): void {
                $page->component('members/Show')
                    ->where('member.id', $member->sqid)
                    ->where('defaultAddress.id', $member->defaultAddress->sqid)
                    ->where('team.id', $member->team->sqid);
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
        $servicePlan = ServicePlan::factory()->recycle($member->team)->createOne();
        $cabinet = Cabinet::factory()
            ->recycle([$member, $member->team])
            ->for($servicePlan)
            ->createOne();

        login(team: $member->team);

        $response = get(route('teams.members.cabinets.show', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($member, $cabinet, $servicePlan): void {
                $page->component('members/cabinets/Show')
                    ->where('member.id', $member->sqid)
                    ->where('cabinet.id', $cabinet->sqid)
                    ->where('cabinet.code', $cabinet->code)
                    ->where('team.id', $member->team->sqid)
                    ->where('servicePlan.id', $servicePlan->sqid);
            });
    });
});
```
