# Store Tests: Uniqueness Phone

POST store: Phone uniqueness: formatted input normalizes before uniqueness and action mapping; preserve duplicate, other tenant and deleted-record variants.

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
            'phone_number' => '+14155550110',
        ]);

        login(team: $member->team);

        $response = post(route('teams.members.store', [
            'team' => $member->team,
        ]), [
            'phone_number' => '+1 415 555 0110',
        ]);

        $response->assertRedirectBackWithErrors([
            'phone_number' => 'The phone number has already been taken.',
        ]);
    });

    it('allows a phone number used in a different scope', function (): void {
        Member::factory()->createOne([
            'phone_number' => '+14155550110',
        ]);

        $team = Team::factory()->createOne();
        $member = Member::factory()->recycle($team)->createOne();

        login(team: $team);

        mock(CreateMember::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Team $teamArgument, CreateMemberInput $input): bool => $teamArgument->is($team)
                && $input->phoneNumber === '+14155550110')
            ->andReturn($member);

        $response = post(route('teams.members.store', [
            'team' => $team,
        ]), [
            'phone_number' => '+1 415 555 0110',
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
                'phone_number' => '+14155550110',
            ]);
        $member = Member::factory()->recycle($deletedMember->team)->createOne();

        login(team: $deletedMember->team);

        mock(CreateMember::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Team $teamArgument, CreateMemberInput $input): bool => $teamArgument->is($deletedMember->team)
                && $input->phoneNumber === '+14155550110')
            ->andReturn($member);

        $response = post(route('teams.members.store', [
            'team' => $deletedMember->team,
        ]), [
            'phone_number' => '+1 415 555 0110',
        ]);

        $response->assertRedirectToRoute('teams.members.show', [
            'team' => $deletedMember->team,
            'member' => $member,
        ])
            ->assertToast('Member created');
    });
});
```
