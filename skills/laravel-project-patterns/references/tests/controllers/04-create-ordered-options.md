# Create Tests: Eligible Options In Name Order

Positive GET create-page test for eligible tenant-owned options in alphabetical order. It asserts exact allowed IDs and labels despite reverse insertion order and excludes foreign options. Use the separate availability case for inactive or deleted options.

Create Zulu before Alpha to prove sorting. Use `shows the create page`; put unavailable-option cases after positive cases.

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
        $secondServicePlan = ServicePlan::factory()
            ->recycle($member->team)
            ->createOne(['name' => 'Zulu Plan']);
        $firstServicePlan = ServicePlan::factory()
            ->recycle($member->team)
            ->createOne(['name' => 'Alpha Plan']);
        ServicePlan::factory()->createOne(['name' => 'Other team']);

        login(team: $member->team);

        $response = get(route('teams.members.enrollments.create', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use (
                $member,
                $firstServicePlan,
                $secondServicePlan,
            ): void {
                $page->component('members/enrollments/Create')
                    ->where('member.id', $member->public_id)
                    ->where('team.id', $member->team->public_id)
                    ->has('servicePlans', 2)
                    ->where('servicePlans.0.id', $firstServicePlan->public_id)
                    ->where('servicePlans.0.name', 'Alpha Plan')
                    ->where('servicePlans.1.id', $secondServicePlan->public_id)
                    ->where('servicePlans.1.name', 'Zulu Plan');
            });
    });
});
```
