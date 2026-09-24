# Update Tests: Mapping Contact Input

PATCH update: Mocked member update checks typed contact input, redirect and toast; no persistence assertion.

## Updates the record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\Members\Inputs\UpdateMemberInput;
use App\Actions\Members\UpdateMember;
use App\Models\Member;

describe('update', function (): void {
    it('updates the record', function (): void {
        $member = Member::factory()->createOne();

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
            'member' => $member->sqid,
        ])
            ->assertToast('Member updated');
    });
});
```
