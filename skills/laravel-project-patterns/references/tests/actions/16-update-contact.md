# Action Tests: Update Contact Fields

Integration tests for contact updates: all fields, omitted-field preservation and explicit-null clearing, with persisted names, notes, email and normalized phone values.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\Members\Inputs\UpdateMemberInput;
use App\Actions\Members\UpdateMember;
use App\Models\Member;

it('updates a record', function (): void {
    $member = Member::factory()->createOne();

    $updatedMember = resolve(UpdateMember::class)->handle(
        $member,
        UpdateMemberInput::from([
            'email' => 'alex@example.net',
            'first_name' => 'Alex',
            'last_name' => 'Example',
            'note' => 'Updated',
            'phone_number' => '+1 415 555 0110',
        ]),
    );

    expect($updatedMember->is($member))->toBeTrue();

    assertDatabaseHas(Member::class, [
        'id' => $member->id,
        'email' => 'alex@example.net',
        'first_name' => 'Alex',
        'last_name' => 'Example',
        'note' => 'Updated',
        'phone_number' => '+14155550110',
    ]);
});

it('updates only provided fields', function (): void {
    $member = Member::factory()->createOne([
        'email' => 'alex@example.net',
        'first_name' => 'Alex',
    ]);

    resolve(UpdateMember::class)->handle(
        $member,
        UpdateMemberInput::from([
            'first_name' => 'Sam',
        ]),
    );

    assertDatabaseHas(Member::class, [
        'id' => $member->id,
        'email' => 'alex@example.net',
        'first_name' => 'Sam',
    ]);
});

it('clears nullable fields', function (): void {
    $member = Member::factory()->createOne([
        'email' => 'alex@example.net',
        'first_name' => 'Alex',
        'last_name' => 'Example',
        'note' => 'Test note',
        'phone_number' => '+14155550110',
    ]);

    resolve(UpdateMember::class)->handle(
        $member,
        UpdateMemberInput::from([
            'email' => null,
            'last_name' => null,
            'note' => null,
            'phone_number' => null,
        ]),
    );

    assertDatabaseHas(Member::class, [
        'id' => $member->id,
        'email' => null,
        'first_name' => 'Alex',
        'last_name' => null,
        'note' => null,
        'phone_number' => null,
    ]);
});
```
