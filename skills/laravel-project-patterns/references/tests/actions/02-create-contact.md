# Action Tests: Create Contact Fields

Integration tests for creating a contact through an input object: full fields and required-only input, returned model type, tenant ownership, nullable defaults and normalized phone storage.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\Members\CreateMember;
use App\Actions\Members\Inputs\CreateMemberInput;
use App\Models\Member;
use App\Models\Team;

it('creates a record', function (): void {
    $team = Team::factory()->createOne();

    $member = resolve(CreateMember::class)->handle(
        $team,
        CreateMemberInput::from([
            'email' => 'alex@example.net',
            'first_name' => 'Alex',
            'last_name' => 'Example',
            'note' => 'Test note',
            'phone_number' => '+1 415 555 0110',
        ]),
    );

    expect($member)->toBeInstanceOf(Member::class);

    assertDatabaseHas(Member::class, [
        'id' => $member->id,
        'team_id' => $team->id,
        'email' => 'alex@example.net',
        'first_name' => 'Alex',
        'last_name' => 'Example',
        'note' => 'Test note',
        'phone_number' => '+14155550110',
    ]);
});

it('creates a record with only required fields', function (): void {
    $team = Team::factory()->createOne();

    $member = resolve(CreateMember::class)->handle(
        $team,
        CreateMemberInput::from([
            'email' => 'alex@example.net',
        ]),
    );

    assertDatabaseHas(Member::class, [
        'id' => $member->id,
        'team_id' => $team->id,
        'email' => 'alex@example.net',
        'first_name' => null,
        'last_name' => null,
        'note' => null,
        'phone_number' => null,
    ]);
});
```
