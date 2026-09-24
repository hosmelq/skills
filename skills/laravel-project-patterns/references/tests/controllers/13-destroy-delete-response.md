# Destroy Tests: Direct Deletion And Controller Response

Complete direct-entity DELETE destroy block: guest login redirect, unrelated tenant 403, foreign or soft-deleted target 404, then one identity-matched delete-action call, named collection redirect and success toast. The action is mocked; persistence is not asserted.

Use team/member routes without additional parents. Keep the canonical names when adapting the entity.

## Direct Entity

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\Members\DeleteMember;
use App\Models\Member;
use App\Models\Team;

describe('destroy', function (): void {
    it('requires authentication', function (): void {
        $member = Member::factory()->createOne();

        $response = delete(route('teams.members.destroy', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('prevents deleting from an unrelated tenant', function (): void {
        $unrelatedMember = Member::factory()->createOne();

        login();

        $response = delete(route('teams.members.destroy', [
            'team' => $unrelatedMember->team,
            'member' => $unrelatedMember,
        ]));

        $response->assertForbidden();
    });

    it('returns not found when the record belongs to another tenant', function (): void {
        $relatedTeam = Team::factory()->createOne();
        $unrelatedMember = Member::factory()->createOne();

        login(team: $relatedTeam);

        $response = delete(route('teams.members.destroy', [
            'team' => $relatedTeam,
            'member' => $unrelatedMember,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record is soft deleted', function (): void {
        $member = Member::factory()->trashed()->createOne();

        login(team: $member->team);

        $response = delete(route('teams.members.destroy', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertNotFound();
    });

    it('deletes the record', function (): void {
        $member = Member::factory()->createOne();

        login(team: $member->team);

        mock(DeleteMember::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Member $memberArgument): bool => $memberArgument->is($member));

        $response = delete(route('teams.members.destroy', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertRedirectToRoute('teams.members.index', [
            'team' => $member->team,
        ])
            ->assertToast('Member deleted');
    });
});
```
