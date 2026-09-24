# Store Tests: Uniqueness Email

Pest POST store: Email uniqueness: duplicate within tenant, reuse across tenant and after deletion; preserve exact action input, detail redirect and toast.

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
    it('rejects a duplicate email in the same scope', function (): void {
        $member = Member::factory()->createOne([
            'email' => 'john@gmail.com',
        ]);

        signIn(team: $member->team);

        $response = post(route('teams.members.store', [
            'team' => $member->team,
        ]), [
            'email' => 'john@gmail.com',
        ]);

        $response->assertRedirectBackWithErrors([
            'email' => 'The email has already been taken.',
        ]);
    });

    it('allows an email used in a different scope', function (): void {
        Member::factory()->createOne([
            'email' => 'john@gmail.com',
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
                && $input->email === 'john@gmail.com')
            ->andReturn($member);

        $response = post(route('teams.members.store', [
            'team' => $team,
        ]), [
            'email' => 'john@gmail.com',
        ]);

        $response->assertRedirectToRoute('teams.members.show', [
            'team' => $team,
            'member' => $member,
        ])
            ->assertToast('Member created');
    });

    it('allows an email used by a soft deleted record', function (): void {
        $deletedMember = Member::factory()
            ->trashed()
            ->createOne([
                'email' => 'john@gmail.com',
            ]);
        $member = Member::factory()
            ->for($deletedMember->team)
            ->createOne();

        signIn(team: $deletedMember->team);

        mock(CreateMember::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Team $teamArgument, CreateMemberInput $input): bool => $teamArgument->is($deletedMember->team)
                && $input->email === 'john@gmail.com')
            ->andReturn($member);

        $response = post(route('teams.members.store', [
            'team' => $deletedMember->team,
        ]), [
            'email' => 'john@gmail.com',
        ]);

        $response->assertRedirectToRoute('teams.members.show', [
            'team' => $deletedMember->team,
            'member' => $member,
        ])
            ->assertToast('Member created');
    });
});
```
