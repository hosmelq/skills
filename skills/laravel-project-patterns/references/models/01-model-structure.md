# Models: Structure and Property Types

Implement an Eloquent model with strict types, grouped property PHPDoc, a typed factory, computed Sqids and soft deletion. Select traits from the inspected lifecycle.

Group properties as keys, attributes, timestamps, then relations. Alphabetize independent imports, attributes and peer methods; preserve meaningful order. Decimal casts expose strings. These dates assume `Date::use(CarbonImmutable::class)` in the application bootstrap.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasSqid;
use Carbon\CarbonImmutable;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read int $id
 * @property-read int $team_id
 * @property-read null|string $email
 * @property-read null|string $first_name
 * @property-read null|string $last_name
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read null|CarbonImmutable $deleted_at
 * @property-read Team $team
 */
class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    use HasSqid;
    use SoftDeletes;

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
```
