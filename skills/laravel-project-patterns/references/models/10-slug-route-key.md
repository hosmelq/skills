# Models: Slug Route Key

Address a model in URLs by a readable slug generated once from its name, with Spatie Sluggable and a `#[RouteKey]` attribute, and test slug stability and the route key.

- `#[RouteKey('slug')]` overrides the Sqid route key from `HasSqid`.
- The migration owns the slug's unique index.
- Tests belong in the integration suite.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasSqid;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\Attributes\Sluggable;

/**
 * @property-read string $name
 * @property-read string $slug
 */
#[RouteKey('slug')]
#[Sluggable(from: 'name', to: 'slug', onUpdate: false)]
final class Clinic extends Model
{
    use HasSqid;
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Models\Clinic;

it('does not regenerate the slug when the clinic name changes', function (): void {
    $clinic = Clinic::factory()->createOne(['name' => 'North Clinic']);

    $clinic->update(['name' => 'South Clinic']);

    assertDatabaseHas(Clinic::class, [
        'id' => $clinic->id,
        'slug' => 'north-clinic',
    ]);
});

it('uses the slug as its route key', function (): void {
    $clinic = Clinic::factory()->createOne(['name' => 'North Clinic']);

    expect($clinic->getRouteKey())
        ->toBe('north-clinic')
        ->and($clinic->getRouteKeyName())
        ->toBe('slug');
});
```
