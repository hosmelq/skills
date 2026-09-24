# Default Record Tests: Ordered Cases

Browser PATCH default selection: ordered access, foreign/deleted parent, wrong same-tenant parent, foreign/deleted record and successful typed action delegation. Preserve the exact nested redirect and toast.

Authenticate the URL tenant for 404 cases. The action is mocked; no database default change is asserted. `login()` creates an outsider; `login(team: ...)` supplies membership.

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

    login();

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

    login(team: $team);

    $response = patch(route('teams.members.addresses.make-default', [
        'team' => $team,
        'member' => $address->member,
        'address' => $address,
    ]));

    $response->assertNotFound();
});

it('returns not found when the parent is soft deleted', function (): void {
    $member = Member::factory()->trashed()->createOne();
    $address = MemberAddress::factory()->recycle($member)->createOne();

    login(team: $member->team);

    $response = patch(route('teams.members.addresses.make-default', [
        'team' => $member->team,
        'member' => $member,
        'address' => $address,
    ]));

    $response->assertNotFound();
});

it('returns not found when the record belongs to another parent in the same tenant', function (): void {
    $member = Member::factory()->createOne();
    $unrelatedAddress = MemberAddress::factory()->recycle($member->team)->createOne();

    login(team: $member->team);

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

    login(team: $member->team);

    $response = patch(route('teams.members.addresses.make-default', [
        'team' => $member->team,
        'member' => $member,
        'address' => $address,
    ]));

    $response->assertNotFound();
});

it('returns not found when the record is soft deleted', function (): void {
    $address = MemberAddress::factory()->trashed()->createOne();

    login(team: $address->member->team);

    $response = patch(route('teams.members.addresses.make-default', [
        'team' => $address->member->team,
        'member' => $address->member,
        'address' => $address,
    ]));

    $response->assertNotFound();
});

it('sets the default record', function (): void {
    $address = MemberAddress::factory()->createOne();

    login(team: $address->member->team);

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
