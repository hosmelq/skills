# Models: Slug Route Key

Implement a slug route key with Laravel RouteKey and Spatie Sluggable attributes, generating from name while preserving the slug on updates. HasSqid still supports explicitly requested Sqid binding.

This example uses Laravel 13 attributes and Sluggable 4. Keep the installed API and slug column contract; do not add a second slug-generation mechanism.

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
class Team extends Model
{
    use HasSqid;
}
```
