# Update Tests: Mapping Settings

Pest PATCH update: Settings update preserves the submitted property mapping; a named dataset checks each enabled flag can become false. All cases assert the redirect and toast.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\Teams\Inputs\UpdateTeamInput;
use App\Actions\Teams\UpdateTeam;
use App\Enums\AssignmentMode;
use App\Models\Team;

describe('update', function (): void {
    it('updates the settings', function (): void {
        $team = Team::factory()->createOne([
            'name' => 'My Team',
        ]);

        mock(UpdateTeam::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(
                fn (Team $actualTeam, UpdateTeamInput $input): bool =>
                    $actualTeam->is($team)
                    && $input->assignmentMode === AssignmentMode::Instant
                    && $input->cabinetsEnabled === true
                    && $input->name === 'Updated Team',
            );

        login(team: $team);

        $response = patch(route('teams.update', [
            'team' => $team,
        ]), [
            'assignment_mode' => AssignmentMode::Instant(),
            'cabinets_enabled' => true,
            'name' => 'Updated Team',
        ]);

        $response->assertRedirectBack()
            ->assertToast('Settings updated');
    });

    it('allows disabling an enabled setting', function (string $field, string $property): void {
        $team = Team::factory()->createOne([
            $field => true,
        ]);

        mock(UpdateTeam::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(
                fn (Team $actualTeam, UpdateTeamInput $input): bool =>
                    $actualTeam->is($team)
                    && $input->{$property} === false,
            );

        login(team: $team);

        $response = patch(route('teams.update', [
            'team' => $team,
        ]), [
            $field => false,
        ]);

        $response->assertRedirectBack()
            ->assertToast('Settings updated');
    })->with([
        'cabinets enabled' => ['cabinets_enabled', 'cabinetsEnabled'],
        'work orders enabled' => ['work_orders_enabled', 'workOrdersEnabled'],
    ]);
});
```
