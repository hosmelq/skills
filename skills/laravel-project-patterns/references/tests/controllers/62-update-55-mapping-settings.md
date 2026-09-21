# Update Tests: Mapping Settings

Pest PATCH update: Settings update preserves the exact submitted property mapping and separately retains both false-valued flags; all successful paths assert the original redirect and toast.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\Teams\Inputs\UpdateTeamInput;
use App\Actions\Teams\UpdateTeam;
use App\Enums\CabinetProvisioningMode;
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
                    && $input->cabinetProvisioningMode === CabinetProvisioningMode::Instant
                    && $input->cabinetsEnabled === true
                    && $input->name === 'Updated Team',
            );

        signIn(team: $team);

        $response = patch(route('teams.update', [
            'team' => $team,
        ]), [
            'cabinet_provisioning_mode' => CabinetProvisioningMode::Instant(),
            'cabinets_enabled' => true,
            'name' => 'Updated Team',
        ]);

        $response->assertRedirectBack()
            ->assertToast('Settings updated');
    });

    it('allows disabling the cabinet setting', function (): void {
        $team = Team::factory()->createOne([
            'cabinets_enabled' => true,
        ]);

        mock(UpdateTeam::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(
                fn (Team $actualTeam, UpdateTeamInput $input): bool =>
                    $actualTeam->is($team)
                    && $input->cabinetsEnabled === false,
            );

        signIn(team: $team);

        $response = patch(route('teams.update', [
            'team' => $team,
        ]), [
            'cabinets_enabled' => false,
        ]);

        $response->assertRedirectBack()
            ->assertToast('Settings updated');
    });

    it('allows disabling the shipment setting', function (): void {
        $team = Team::factory()->createOne([
            'shipments_enabled' => true,
        ]);

        mock(UpdateTeam::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(
                fn (Team $actualTeam, UpdateTeamInput $input): bool =>
                    $actualTeam->is($team)
                    && $input->shipmentsEnabled === false,
            );

        signIn(team: $team);

        $response = patch(route('teams.update', [
            'team' => $team,
        ]), [
            'shipments_enabled' => false,
        ]);

        $response->assertRedirectBack()
            ->assertToast('Settings updated');
    });
});
```
