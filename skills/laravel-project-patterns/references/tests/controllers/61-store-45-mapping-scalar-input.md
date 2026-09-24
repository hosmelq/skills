# Store Tests: Mapping Scalar Input

Pest POST store: Two complete scalar-input examples: email and name. Different required payloads and detail routes are retained rather than declared equivalent.

## Stores the record — variant 1

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
    it('stores the record', function (): void {
        $team = Team::factory()->createOne();
        $member = Member::factory()->recycle($team)->createOne();

        login(team: $team);

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
});
```

## Stores the record — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\Facilities\CreateFacility;
use App\Actions\Facilities\Inputs\CreateFacilityInput;
use App\Enums\FacilityType;
use App\Models\Facility;
use App\Models\Team;

describe('store', function (): void {
    it('stores the record', function (): void {
        $team = Team::factory()->createOne();
        $facility = Facility::factory()->recycle($team)->createOne();

        login(team: $team);

        mock(CreateFacility::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Team $teamArgument, CreateFacilityInput $input): bool => $teamArgument->is($team)
                && $input->name === 'Main Studio')
            ->andReturn($facility);

        $response = post(route('teams.facilities.store', [
            'team' => $team,
        ]), [
            'country_code' => 'US',
            'name' => 'Main Studio',
            'type' => FacilityType::Studio(),
        ]);

        $response->assertRedirectToRoute('teams.facilities.show', [
            'team' => $team,
            'facility' => $facility,
        ])
            ->assertToast('Facility created');
    });
});
```
