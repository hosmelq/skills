# Create Tests: Exact Label and Value Options

GET create page with an exact label/value option array, encoded Sqid values and scoped parent IDs. Use this shape when the prop contains select options; resource objects with id/name fields have a different contract.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Member;
use App\Models\ServicePlan;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('shows the create page', function (): void {
        $member = Member::factory()->createOne();
        $servicePlan = ServicePlan::factory()
            ->recycle($member->team)
            ->createOne();

        login(team: $member->team);

        $response = get(route('teams.members.cabinets.create', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($member, $servicePlan): void {
                $page->component('members/cabinets/Create')
                    ->where('member.id', $member->sqid)
                    ->where('team.id', $member->team->sqid)
                    ->where('servicePlans', [
                        [
                            'label' => $servicePlan->name,
                            'value' => $servicePlan->sqid,
                        ],
                    ]);
            });
    });
});
```
