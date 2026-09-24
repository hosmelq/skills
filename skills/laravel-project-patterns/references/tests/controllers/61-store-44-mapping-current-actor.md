# Store Tests: Mapping Current Actor

POST store: Current actor and typed settings input passed to action; returned record selects settings redirect and toast.

## Stores the record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\Teams\CreateTeam;
use App\Actions\Teams\Inputs\CreateTeamInput;
use App\Enums\AssignmentMode;
use App\Enums\CountryCode;
use App\Models\Team;
use App\Models\User;

describe('store', function (): void {
    it('stores the record', function (): void {
        $user = login();
        $team = Team::factory()->createOne();

        mock(CreateTeam::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(
                fn (User $actualUser, CreateTeamInput $input): bool =>
                    $actualUser->is($user)
                    && $input->assignmentMode === AssignmentMode::Instant
                    && $input->cabinetsEnabled === true
                    && $input->name === 'My Team',
            )
            ->andReturn($team);

        $response = post(route('teams.store'), [
            'assignment_mode' => AssignmentMode::Instant(),
            'cabinets_enabled' => true,
            'country_code' => CountryCode::UnitedStates(),
            'name' => 'My Team',
            'timezone' => 'America/Los_Angeles',
        ]);

        $response->assertRedirectToRoute('teams.settings.general', [
            'team' => $team,
        ])
            ->assertToast('Team created');
    });
});
```
