# Models: Membership Pivot

Implement a bidirectional belongsToMany membership relation using a custom incrementing pivot, membership alias and pivot timestamps. Preserve the relation chain and four-part generic.

The pivot table must contain its incrementing ID and timestamps. The relationship methods belong on the two models shown.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Override;

class Membership extends Pivot
{
    #[Override]
    public $incrementing = true;
}
```

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
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

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * @return BelongsToMany<Team, $this, Membership, 'membership'>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
            ->as('membership')
            ->using(Membership::class)
            ->withTimestamps();
    }
}
```
