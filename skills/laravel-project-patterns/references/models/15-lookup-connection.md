# Models: Lookup Models on Another Connection

Implement imported lookup models with an explicit Connection attribute and typed parent/child relations. Their external schema determines attribute types; do not infer casts or lifecycle traits from PHPDoc.

Configure the named connection and match its actual tables and columns. These examples retain conventional table names and timestamp behavior; they do not add factories, Sqids, soft deletion or casts.

```php
<?php

declare(strict_types=1);

namespace App\Models\Lookup;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read Collection<int, Area> $areas
 * @property-read Collection<int, Place> $places
 */
#[Connection('catalog')]
class Region extends Model
{
    /**
     * @return HasMany<Area, $this>
     */
    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    /**
     * @return HasMany<Place, $this>
     */
    public function places(): HasMany
    {
        return $this->hasMany(Place::class);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Models\Lookup;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read int $region_id
 * @property-read string $name
 * @property-read Collection<int, Place> $places
 * @property-read Region $region
 */
#[Connection('catalog')]
class Area extends Model
{
    /**
     * @return HasMany<Place, $this>
     */
    public function places(): HasMany
    {
        return $this->hasMany(Place::class);
    }

    /**
     * @return BelongsTo<Region, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Models\Lookup;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $area_id
 * @property-read int $region_id
 * @property-read string $name
 * @property-read Area $area
 * @property-read Region $region
 */
#[Connection('catalog')]
class Place extends Model
{
    /**
     * @return BelongsTo<Area, $this>
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * @return BelongsTo<Region, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
```
