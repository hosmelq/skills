# Model Tests: Slug Lifecycle

Persisted model slug generation on creation, preservation after a name change, and the slug value/name used for route binding.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Models\Team;

it('generates a slug on creation', function (): void {
    $team = Team::factory()->createOne([
        'name' => 'Example Team',
    ]);

    assertDatabaseHas(Team::class, [
        'id' => $team->id,
        'slug' => 'example-team',
    ]);
});

it('preserves the slug when the name changes', function (): void {
    $team = Team::factory()->createOne([
        'name' => 'Example Team',
    ]);

    $team->update([
        'name' => 'Renamed Team',
    ]);

    assertDatabaseHas(Team::class, [
        'id' => $team->id,
        'slug' => 'example-team',
    ]);
});

it('uses the slug as the route key', function (): void {
    $team = Team::factory()->createOne([
        'name' => 'Example Team',
    ]);

    expect($team->getRouteKey())
        ->toBe('example-team')
        ->and($team->getRouteKeyName())
        ->toBe('slug');
});
```
