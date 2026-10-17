# Models: Membership Relations

Add a many-to-many relation whose pivot table records membership between two models, with a custom pivot model, a named pivot accessor and pivot timestamps on both sides.

- Declare the same `as()`, `using()` and `withTimestamps()` chain on both sides.
- Pass the pivot table name only when it breaks Laravel's convention.
- Plain relations need no test.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read Collection<int, User> $users
 */
final class Clinic extends Model
{
    /**
     * @return BelongsToMany<User, $this, Membership, 'membership'>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->as('membership')
            ->using(Membership::class)
            ->withTimestamps();
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property-read Collection<int, Clinic> $clinics
 */
final class User extends Authenticatable
{
    /**
     * @return BelongsToMany<Clinic, $this, Membership, 'membership'>
     */
    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class)
            ->as('membership')
            ->using(Membership::class)
            ->withTimestamps();
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Override;

final class Membership extends Pivot
{
    #[Override]
    public $incrementing = true;
}
```
