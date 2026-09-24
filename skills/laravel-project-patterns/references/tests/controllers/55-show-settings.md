# Show Tests: Tenant Settings

Complete GET tenant settings response asserts its specific component, tenant public ID and exact settings enum options. This route has no nested record parameter.

## Settings Page

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\CabinetProvisioningMode;
use App\Models\Team;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('shows the settings page', function (): void {
        $team = Team::factory()->createOne();

        login(team: $team);

        $response = get(route('teams.settings.general', [
            'team' => $team,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($team): void {
                $page->component('team/Settings')
                    ->where('cabinetProvisioningModes', CabinetProvisioningMode::options())
                    ->where('team.id', $team->public_id);
            });
    });
});
```
