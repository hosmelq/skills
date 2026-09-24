# Action Tests: Dispatch on a Setting Transition

Integration tests for a provisioning job triggered only when a boolean setting changes from disabled to enabled. Assert no dispatch when disabling it or leaving it enabled; job execution is outside this test.

```php
<?php

declare(strict_types=1);

use App\Actions\Teams\Inputs\UpdateTeamInput;
use App\Actions\Teams\UpdateTeam;
use App\Jobs\ProvisionTeamCabinets;
use App\Models\Team;
use Illuminate\Support\Facades\Bus;

it('does not dispatch assignment provisioning when disabling assignment self-service', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => true]);

    Bus::fake();

    resolve(UpdateTeam::class)->handle(
        team: $team,
        input: UpdateTeamInput::from([
            'cabinets_enabled' => false,
        ]),
    );

    Bus::assertNotDispatched(ProvisionTeamCabinets::class);
});

it('does not dispatch assignment provisioning when assignment self-service remains enabled', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => true]);

    Bus::fake();

    resolve(UpdateTeam::class)->handle(
        team: $team,
        input: UpdateTeamInput::from([
            'name' => 'Updated Team',
        ]),
    );

    Bus::assertNotDispatched(ProvisionTeamCabinets::class);
});

it('dispatches assignment provisioning when enabling assignment self-service', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => false]);

    Bus::fake();

    resolve(UpdateTeam::class)->handle(
        team: $team,
        input: UpdateTeamInput::from([
            'cabinets_enabled' => true,
        ]),
    );

    Bus::assertDispatched(
        ProvisionTeamCabinets::class,
        fn (ProvisionTeamCabinets $job): bool => $job->team->is($team),
    );
});
```
