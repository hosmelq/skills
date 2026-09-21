# Show Tests: Tenant Settings Authentication

Browser GET show tenant settings authentication: a guest redirects to login and an unrelated authenticated tenant gets 403. Keep every valid route parameter for this route depth.

## Authentication And Access

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Team;

describe('show', function (): void {
    it('requires authentication', function (): void {
        $team = Team::factory()->createOne();

        $response = get(route('teams.settings.general', [
            'team' => $team,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('prevents viewing from an unrelated tenant', function (): void {
        $unrelatedTeam = Team::factory()->createOne();

        $userTeam = Team::factory()->createOne();

        signIn(team: $userTeam);

        $response = get(route('teams.settings.general', [
            'team' => $unrelatedTeam,
        ]));

        $response->assertForbidden();
    });
});
```
