# Store Tests: Uniqueness Phone

Pest POST store: Phone uniqueness: formatted input normalizes before uniqueness and action mapping; preserve duplicate, other tenant and deleted-record variants.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\Members\CreateMember;
use App\Actions\Members\Inputs\CreateMemberInput;
use App\Models\Member;
use App\Models\Team;

describe('store', function (): void {
    it('rejects a duplicate phone number in the same scope', function (): void {
        $member = Member::factory()->createOne([
            'phone_number' => '+50588888888',
        ]);

        signIn(team: $member->team);

        $response = post(route('teams.members.store', [
            'team' => $member->team,
        ]), [
            'phone_number' => '+505 8888 8888',
        ]);

        $response->assertRedirectBackWithErrors([
            'phone_number' => 'The phone number has already been taken.',
        ]);
    });

    it('allows a phone number used in a different scope', function (): void {
        Member::factory()->createOne([
            'phone_number' => '+50588888888',
        ]);

        $team = Team::factory()->createOne();
        $member = Member::factory()
            ->for($team)
            ->createOne();

        signIn(team: $team);

        mock(CreateMember::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Team $teamArgument, CreateMemberInput $input): bool => $teamArgument->is($team)
                && $input->phoneNumber === '+50588888888')
            ->andReturn($member);

        $response = post(route('teams.members.store', [
            'team' => $team,
        ]), [
            'phone_number' => '+505 8888 8888',
        ]);

        $response->assertRedirectToRoute('teams.members.show', [
            'team' => $team,
            'member' => $member,
        ])
            ->assertToast('Member created');
    });

    it('allows a phone number used by a soft deleted record', function (): void {
        $deletedMember = Member::factory()
            ->trashed()
            ->createOne([
                'phone_number' => '+50588888888',
            ]);
        $member = Member::factory()
            ->for($deletedMember->team)
            ->createOne();

        signIn(team: $deletedMember->team);

        mock(CreateMember::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Team $teamArgument, CreateMemberInput $input): bool => $teamArgument->is($deletedMember->team)
                && $input->phoneNumber === '+50588888888')
            ->andReturn($member);

        $response = post(route('teams.members.store', [
            'team' => $deletedMember->team,
        ]), [
            'phone_number' => '+505 8888 8888',
        ]);

        $response->assertRedirectToRoute('teams.members.show', [
            'team' => $deletedMember->team,
            'member' => $member,
        ])
            ->assertToast('Member created');
    });
});
```
