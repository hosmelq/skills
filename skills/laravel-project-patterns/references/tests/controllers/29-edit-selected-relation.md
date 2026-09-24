# Edit Tests: Current Live Deleted And Inactive Relation

GET edit preserves a singular current related resource when live, soft deleted or inactive: exact public ID/name plus serialized deleted_at or deactivated_at timestamp. Complete nested page examples also assert tenant, parent and target IDs; this resource is distinct from an option list.

Keep the live case first, then soft-deleted and inactive relation variants. Restrict these expectations to relationships the inspected edit endpoint deliberately loads with their historical state.

## Current Relation

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Cabinet;
use App\Models\Member;
use App\Models\ServicePlan;
use Inertia\Testing\AssertableInertia;

describe('edit', function (): void {
    it('shows the edit page', function (): void {
        $member = Member::factory()->createOne();
        $servicePlan = ServicePlan::factory()->recycle($member->team)->createOne();
        $cabinet = Cabinet::factory()
            ->recycle([$member, $member->team])
            ->for($servicePlan)
            ->createOne();

        login(team: $member->team);

        $response = get(route('teams.members.cabinets.edit', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($member, $cabinet, $servicePlan): void {
                $page->component('members/cabinets/Edit')
                    ->where('member.id', $member->sqid)
                    ->where('cabinet.id', $cabinet->sqid)
                    ->where('team.id', $member->team->sqid)
                    ->where('servicePlan.id', $servicePlan->sqid)
                    ->where('servicePlan.name', $servicePlan->name);
            });
    });

    it('shows the edit page with the current soft deleted relation', function (): void {
        $member = Member::factory()->createOne();
        $servicePlan = ServicePlan::factory()->trashed()->recycle($member->team)->createOne();
        $cabinet = Cabinet::factory()
            ->recycle([$member, $member->team])
            ->for($servicePlan)
            ->createOne();

        login(team: $member->team);

        $response = get(route('teams.members.cabinets.edit', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($member, $cabinet, $servicePlan): void {
                $page->component('members/cabinets/Edit')
                    ->where('member.id', $member->sqid)
                    ->where('cabinet.id', $cabinet->sqid)
                    ->where('team.id', $member->team->sqid)
                    ->where('servicePlan.deleted_at', $servicePlan->deleted_at->toJSON())
                    ->where('servicePlan.id', $servicePlan->sqid)
                    ->where('servicePlan.name', $servicePlan->name);
            });
    });

    it('shows the edit page with the current inactive relation', function (): void {
        $member = Member::factory()->createOne();
        $servicePlan = ServicePlan::factory()->deactivated()->recycle($member->team)->createOne();
        $cabinet = Cabinet::factory()
            ->recycle([$member, $member->team])
            ->for($servicePlan)
            ->createOne();

        login(team: $member->team);

        $response = get(route('teams.members.cabinets.edit', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($member, $cabinet, $servicePlan): void {
                $page->component('members/cabinets/Edit')
                    ->where('member.id', $member->sqid)
                    ->where('cabinet.id', $cabinet->sqid)
                    ->where('team.id', $member->team->sqid)
                    ->where('servicePlan.deactivated_at', $servicePlan->deactivated_at->toJSON())
                    ->where('servicePlan.id', $servicePlan->sqid)
                    ->where('servicePlan.name', $servicePlan->name);
            });
    });
});
```
