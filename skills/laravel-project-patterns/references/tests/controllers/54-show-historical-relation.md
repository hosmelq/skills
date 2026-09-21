# Show Tests: Deleted And Inactive Selected Relations

Complete GET show examples retain the current soft deleted or inactive lateral relation of a live record. Preserve related ID/name, exact deleted_at or deactivated_at JSON timestamp, public child fields and route IDs.

## Soft Deleted Relation

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Cabinet;
use App\Models\Member;
use App\Models\ServicePlan;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('shows the detail page with the current soft deleted relation', function (): void {
        $member = Member::factory()->createOne();
        $servicePlan = ServicePlan::factory()
            ->trashed()
            ->for($member->team)
            ->createOne();
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
                    ->where('servicePlan.deleted_at', $servicePlan->deleted_at->toJSON())
                    ->where('servicePlan.id', $servicePlan->public_id)
                    ->where('servicePlan.name', $servicePlan->name);
            });
    });
});
```

## Inactive Relation

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Cabinet;
use App\Models\Member;
use App\Models\ServicePlan;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('shows the detail page with the current inactive relation', function (): void {
        $member = Member::factory()->createOne();
        $servicePlan = ServicePlan::factory()
            ->deactivated()
            ->for($member->team)
            ->createOne();
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
                    ->where('servicePlan.deactivated_at', $servicePlan->deactivated_at->toJSON())
                    ->where('servicePlan.id', $servicePlan->public_id)
                    ->where('servicePlan.name', $servicePlan->name);
            });
    });
});
```
