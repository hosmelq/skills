# Default Record Tests: Ordered Cases

Pest browser PATCH default selection: ordered access, foreign/deleted parent, wrong same-tenant parent, foreign/deleted record and successful typed action delegation. Preserve the exact nested redirect and toast.

Keep the top-level case order below and authenticate the URL tenant for 404 cases. The action is mocked; no database default change is asserted. `signIn()` creates an outsider; `signIn(team: ...)` supplies membership.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\SetDefaultMemberAddress;
use App\Models\Member;
use App\Models\MemberAddress;
use App\Models\Team;

it('requires authentication', function (): void {
    $address = MemberAddress::factory()->createOne();

    $response = patch(route('teams.members.addresses.make-default', [
        'team' => $address->member->team,
        'member' => $address->member,
        'address' => $address,
    ]));

    $response->assertRedirectToRoute('login');
});

it('prevents setting the default record from an unrelated tenant', function (): void {
    $address = MemberAddress::factory()->createOne();

    signIn();

    $response = patch(route('teams.members.addresses.make-default', [
        'team' => $address->member->team,
        'member' => $address->member,
        'address' => $address,
    ]));

    $response->assertForbidden();
});

it('returns not found when the parent belongs to another tenant', function (): void {
    $address = MemberAddress::factory()->createOne();
    $team = Team::factory()->createOne();

    signIn(team: $team);

    $response = patch(route('teams.members.addresses.make-default', [
        'team' => $team,
        'member' => $address->member,
        'address' => $address,
    ]));

    $response->assertNotFound();
});

it('returns not found when the parent is soft deleted', function (): void {
    $member = Member::factory()->trashed()->createOne();
    $address = MemberAddress::factory()
        ->for($member)
        ->createOne();

    signIn(team: $member->team);

    $response = patch(route('teams.members.addresses.make-default', [
        'team' => $member->team,
        'member' => $member,
        'address' => $address,
    ]));

    $response->assertNotFound();
});

it('returns not found when the record belongs to another parent in the same tenant', function (): void {
    $member = Member::factory()->createOne();

    $unrelatedAddress = MemberAddress::factory()
        ->for(Member::factory()->recycle($member->team)->createOne())
        ->createOne();

    signIn(team: $member->team);

    $response = patch(route('teams.members.addresses.make-default', [
        'team' => $member->team,
        'member' => $member,
        'address' => $unrelatedAddress,
    ]));

    $response->assertNotFound();
});

it('returns not found when the record belongs to another tenant', function (): void {
    $member = Member::factory()->createOne();
    $address = MemberAddress::factory()->createOne();

    signIn(team: $member->team);

    $response = patch(route('teams.members.addresses.make-default', [
        'team' => $member->team,
        'member' => $member,
        'address' => $address,
    ]));

    $response->assertNotFound();
});

it('returns not found when the record is soft deleted', function (): void {
    $address = MemberAddress::factory()->trashed()->createOne();

    signIn(team: $address->member->team);

    $response = patch(route('teams.members.addresses.make-default', [
        'team' => $address->member->team,
        'member' => $address->member,
        'address' => $address,
    ]));

    $response->assertNotFound();
});

it('sets the default record', function (): void {
    $address = MemberAddress::factory()->createOne();

    signIn(team: $address->member->team);

    mock(SetDefaultMemberAddress::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (MemberAddress $addressArgument): bool => $addressArgument->is($address));

    $response = patch(route('teams.members.addresses.make-default', [
        'team' => $address->member->team,
        'member' => $address->member,
        'address' => $address,
    ]));

    $response->assertRedirectToRoute('teams.members.addresses.index', [
        'team' => $address->member->team,
        'member' => $address->member,
    ])
        ->assertToast('Default address updated');
});
```
