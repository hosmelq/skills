# Model Tests: Membership and Current Selection

Model membership and ownership predicates, lazy current-tenant persistence, rejected unrelated switching and successful related switching. Preserve owner and member roles.

The owner relation is role-specific: retain `for($user, 'owner')`. Refresh after attaching membership; the current-relation access intentionally triggers a database write.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Models\Team;
use App\Models\User;

it('detects membership before and after attachment', function (): void {
    $team = Team::factory()->createOne();
    $user = User::factory()->createOne();

    expect($user->belongsToTeam($team))->toBeFalse();

    $team->users()->attach($user);

    $user->refresh();

    expect($user->belongsToTeam($team))->toBeTrue();
});

it('detects ownership independently of unrelated records', function (): void {
    $user = User::factory()->createOne();
    $userTeam = Team::factory()->for($user, 'owner')->createOne();
    $unrelatedTeam = Team::factory()->createOne();

    expect($user)
        ->ownsTeam($userTeam)->toBeTrue()
        ->ownsTeam($unrelatedTeam)->toBeFalse();
});

it('persists the current tenant when first loaded', function (): void {
    $user = User::factory()
        ->has(Team::factory(), 'teams')
        ->createOne();
    $team = $user->teams()->sole();

    expect($user->current_team_id)->toBeNull();

    $user->currentTeam;

    assertDatabaseHas(User::class, [
        'id' => $user->id,
        'current_team_id' => $team->id,
    ]);
});

it('prevents switching to an unrelated tenant', function (): void {
    $unrelatedTeam = Team::factory()->createOne();

    $user = User::factory()->createOne();

    expect($user)
        ->switchTeam($unrelatedTeam)->toBeFalse()
        ->currentTeam->toBeNull();
});

it('switches to a related tenant', function (): void {
    $team = Team::factory()->createOne();
    $user = User::factory()->withTeam($team)->createOne();

    expect($user)
        ->switchTeam($team)->toBeTrue()
        ->currentTeam->is($team)->toBeTrue();
});
```
