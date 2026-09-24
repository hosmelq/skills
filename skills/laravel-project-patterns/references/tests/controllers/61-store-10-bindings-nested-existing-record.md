# Store Tests: Bindings Nested Existing Record

Pest POST store: Three bindings on an existing record: parent foreign/deleted, record wrong parent/tenant/deleted, and direct record tenant contradicting parent tenant.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Cabinet;
use App\Models\Member;
use App\Models\Team;

describe('store', function (): void {
    it('returns not found when the parent belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $cabinet = Cabinet::factory()->createOne();

        login(team: $team);

        $response = post(route('teams.members.cabinets.deactivation.store', [
            'team' => $team,
            'member' => $cabinet->member,
            'cabinet' => $cabinet,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent is soft deleted', function (): void {
        $member = Member::factory()->trashed()->createOne();
        $cabinet = Cabinet::factory()->createOne([
            'member_id' => $member->id,
            'team_id' => $member->team_id,
        ]);

        login(team: $member->team);

        $response = post(route('teams.members.cabinets.deactivation.store', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record belongs to another parent in the same tenant', function (): void {
        $member = Member::factory()->createOne();
        $cabinet = Cabinet::factory()->recycle($member->team)->createOne();

        login(team: $member->team);

        $response = post(route('teams.members.cabinets.deactivation.store', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record belongs to another tenant', function (): void {
        $member = Member::factory()->createOne();
        $cabinet = Cabinet::factory()->createOne();

        login(team: $member->team);

        $response = post(route('teams.members.cabinets.deactivation.store', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record is soft deleted', function (): void {
        $cabinet = Cabinet::factory()->trashed()->createOne();

        login(team: $cabinet->member->team);

        $response = post(route('teams.members.cabinets.deactivation.store', [
            'team' => $cabinet->member->team,
            'member' => $cabinet->member,
            'cabinet' => $cabinet,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record tenant does not match its parent tenant', function (): void {
        $member = Member::factory()->createOne();
        $otherTeam = Team::factory()->createOne();
        $cabinet = Cabinet::factory()
            ->for($member)
            ->for($otherTeam)
            ->createOne();

        login(team: $member->team);

        $response = post(route('teams.members.cabinets.deactivation.store', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ]));

        $response->assertNotFound();
    });
});
```
