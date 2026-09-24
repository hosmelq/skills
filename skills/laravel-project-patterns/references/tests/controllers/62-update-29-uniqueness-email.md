# Update Tests: Uniqueness Email

Pest PATCH update: Email uniqueness: reject a duplicate in the same tenant; allow the current value, another tenant and a soft-deleted record. Keep positive action-input assertions and responses.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\Members\Inputs\UpdateMemberInput;
use App\Actions\Members\UpdateMember;
use App\Models\Member;

describe('update', function (): void {
    it('rejects a duplicate email in the same scope', function (): void {
        $member1 = Member::factory()->createOne([
            'email' => 'john@gmail.com',
        ]);
        $member2 = Member::factory()->recycle($member1->team)->createOne([
            'email' => 'jane@gmail.com',
        ]);

        login(team: $member1->team);

        $response = patch(route('teams.members.update', [
            'team' => $member1->team,
            'member' => $member2,
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

        $member = Member::factory()->createOne([
            'email' => 'jane@gmail.com',
        ]);

        login(team: $member->team);

        mock(UpdateMember::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Member $memberArgument, UpdateMemberInput $input): bool => $memberArgument->is($member)
                && $input->email === 'john@gmail.com');

        $response = patch(route('teams.members.update', [
            'team' => $member->team,
            'member' => $member,
        ]), [
            'email' => 'john@gmail.com',
        ]);

        $response->assertRedirectToRoute('teams.members.show', [
            'team' => $member->team,
            'member' => $member->public_id,
        ])
            ->assertToast('Member updated');
    });

    it('allows the current email', function (): void {
        $member = Member::factory()->createOne([
            'email' => 'john@gmail.com',
        ]);

        login(team: $member->team);

        mock(UpdateMember::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Member $memberArgument, UpdateMemberInput $input): bool => $memberArgument->is($member)
                && $input->email === 'john@gmail.com');

        $response = patch(route('teams.members.update', [
            'team' => $member->team,
            'member' => $member,
        ]), [
            'email' => $member->email,
        ]);

        $response->assertRedirectToRoute('teams.members.show', [
            'team' => $member->team,
            'member' => $member->public_id,
        ])
            ->assertToast('Member updated');
    });

    it('allows an email used by a soft deleted record', function (): void {
        $deletedMember = Member::factory()
            ->trashed()
            ->createOne([
                'email' => 'john@gmail.com',
            ]);
        $member = Member::factory()->recycle($deletedMember->team)->createOne([
            'email' => 'jane@gmail.com',
        ]);

        login(team: $deletedMember->team);

        mock(UpdateMember::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Member $memberArgument, UpdateMemberInput $input): bool => $memberArgument->is($member)
                && $input->email === 'john@gmail.com');

        $response = patch(route('teams.members.update', [
            'team' => $member->team,
            'member' => $member,
        ]), [
            'email' => 'john@gmail.com',
        ]);

        $response->assertRedirectToRoute('teams.members.show', [
            'team' => $member->team,
            'member' => $member->public_id,
        ])
            ->assertToast('Member updated');
    });
});
```
