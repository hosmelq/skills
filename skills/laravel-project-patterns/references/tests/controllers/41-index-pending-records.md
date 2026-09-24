# Index Tests: Pending Records In Request Order

Complete GET index example returns exactly two pending records ordered by request time while excluding approved and rejected records. It asserts both positions, component and tenant ID.

## Pending Records

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Enrollment;
use App\Models\Member;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('lists pending records newest first', function (): void {
        $member = Member::factory()->createOne();
        $olderEnrollment = Enrollment::factory()
            ->recycle($member)
            ->createOne(['requested_at' => now()->subDay()]);
        $recentEnrollment = Enrollment::factory()
            ->recycle($member->team)
            ->createOne(['requested_at' => now()]);

        Enrollment::factory()
            ->approved()
            ->recycle($member->team)
            ->createOne();

        Enrollment::factory()
            ->rejected()
            ->recycle($member->team)
            ->createOne();

        login(team: $member->team);

        $response = get(route('teams.enrollments.index', [
            'team' => $member->team,
        ]));

        $response->assertOk()
            ->assertInertia(function (
                AssertableInertia $page,
            ) use ($member, $olderEnrollment, $recentEnrollment): void {
                $page->component('enrollments/Index')
                    ->has('enrollments.data', 2)
                    ->where('enrollments.data.0.id', $recentEnrollment->sqid)
                    ->where('enrollments.data.1.id', $olderEnrollment->sqid)
                    ->where('team.id', $member->team->sqid);
            });
    });
});
```
