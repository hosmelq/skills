# Update Tests: Uniqueness Phone

Pest PATCH update: Phone uniqueness: reject a duplicate in the same tenant; allow the current value, another tenant and a soft-deleted record. Keep normalization and typed input checks.

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
    it('rejects a duplicate phone number in the same scope', function (): void {
        $member1 = Member::factory()->createOne([
            'phone_number' => '+50588888888',
        ]);
        $member2 = Member::factory()->recycle($member1->team)->createOne([
            'phone_number' => '+50588889999',
        ]);

        login(team: $member1->team);

        $response = patch(route('teams.members.update', [
            'team' => $member1->team,
            'member' => $member2,
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

        $member = Member::factory()->createOne([
            'phone_number' => '+50588889999',
        ]);

        login(team: $member->team);

        mock(UpdateMember::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Member $memberArgument, UpdateMemberInput $input): bool => $memberArgument->is($member)
                && $input->phoneNumber === '+50588888888');

        $response = patch(route('teams.members.update', [
            'team' => $member->team,
            'member' => $member,
        ]), [
            'phone_number' => '+505 8888 8888',
        ]);

        $response->assertRedirectToRoute('teams.members.show', [
            'team' => $member->team,
            'member' => $member->public_id,
        ])
            ->assertToast('Member updated');
    });

    it('allows the current phone number', function (): void {
        $member = Member::factory()->createOne([
            'phone_number' => '+50588888888',
        ]);

        login(team: $member->team);

        mock(UpdateMember::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Member $memberArgument, UpdateMemberInput $input): bool => $memberArgument->is($member)
                && $input->phoneNumber === '+50588888888');

        $response = patch(route('teams.members.update', [
            'team' => $member->team,
            'member' => $member,
        ]), [
            'phone_number' => '+505 8888 8888',
        ]);

        $response->assertRedirectToRoute('teams.members.show', [
            'team' => $member->team,
            'member' => $member->public_id,
        ])
            ->assertToast('Member updated');
    });

    it('allows a phone number used by a soft deleted record', function (): void {
        $deletedMember = Member::factory()
            ->trashed()
            ->createOne([
                'phone_number' => '+50588888888',
            ]);
        $member = Member::factory()->recycle($deletedMember->team)->createOne([
            'phone_number' => '+50588889999',
        ]);

        login(team: $deletedMember->team);

        mock(UpdateMember::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Member $memberArgument, UpdateMemberInput $input): bool => $memberArgument->is($member)
                && $input->phoneNumber === '+50588888888');

        $response = patch(route('teams.members.update', [
            'team' => $member->team,
            'member' => $member,
        ]), [
            'phone_number' => '+505 8888 8888',
        ]);

        $response->assertRedirectToRoute('teams.members.show', [
            'team' => $member->team,
            'member' => $member->public_id,
        ])
            ->assertToast('Member updated');
    });
});
```
