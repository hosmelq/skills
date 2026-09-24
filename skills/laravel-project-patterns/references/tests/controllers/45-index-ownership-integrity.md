# Index Tests: Conflicting Record And Parent Ownership

Two distinct GET index integrity examples: correct parent with a foreign record tenant, and correct record tenant with a foreign parent. Each asserts exactly one included row and explicitly excludes the conflicting row.

Apply only where the inspected endpoint enforces this contract. Ordinary foreign-tenant exclusion alone does not establish either conflicting-ownership rule.

## Record Tenant Conflicts

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Cabinet;
use App\Models\Team;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('excludes records whose tenant does not match their parent tenant', function (): void {
        $cabinet = Cabinet::factory()->createOne();
        $otherTeam = Team::factory()->createOne();
        $unrelatedCabinet = Cabinet::factory()
            ->for($cabinet->member)
            ->for($otherTeam)
            ->createOne();

        login(team: $cabinet->member->team);

        $response = get(route('teams.members.cabinets.index', [
            'team' => $cabinet->member->team,
            'member' => $cabinet->member,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($cabinet, $unrelatedCabinet): void {
                $page->component('members/cabinets/Index')
                    ->has('cabinets.data', 1, function (AssertableInertia $json) use ($cabinet, $unrelatedCabinet): void {
                        $json
                            ->where('id', $cabinet->public_id)
                            ->whereNot('id', $unrelatedCabinet->public_id)
                            ->etc();
                    });
            });
    });
});
```

## Parent Tenant Conflicts

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Enrollment;
use App\Models\Member;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('excludes records whose parent belongs to another tenant', function (): void {
        $member = Member::factory()->createOne();
        $enrollment = Enrollment::factory()->recycle($member)->createOne();
        $unrelatedMember = Member::factory()->createOne();
        $unrelatedEnrollment = Enrollment::factory()
            ->for($unrelatedMember)
            ->for($member->team)
            ->createOne();

        login(team: $member->team);

        $response = get(route('teams.enrollments.index', [
            'team' => $member->team,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($enrollment, $unrelatedEnrollment): void {
                $page->component('enrollments/Index')
                    ->has('enrollments.data', 1, function (AssertableInertia $json) use ($enrollment, $unrelatedEnrollment): void {
                        $json
                            ->where('id', $enrollment->public_id)
                            ->whereNot('id', $unrelatedEnrollment->public_id)
                            ->etc();
                    });
            });
    });
});
```
