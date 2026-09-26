# Models: Parent and Role Relations

Implement typed belongsTo relations with inferred or explicit foreign keys, including nullable role relations. Match method names, foreign keys and PHPDoc to the schema.

`belongsTo` infers the key from the method name. Keep the inspected explicit keys; nullable or missing related rows do not become non-null through a return annotation.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read null|int $current_facility_id
 * @property-read int $member_id
 * @property-read null|Facility $currentFacility
 * @property-read Member $member
 */
class WorkOrder extends Model
{
    /**
     * @return BelongsTo<Facility, $this>
     */
    public function currentFacility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'current_facility_id');
    }

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
```

This role key is explicit by convention. When an alias differs from the column, the key is required: `rule()` for `plan_rule_id` must return `$this->belongsTo(PlanRule::class, 'plan_rule_id')`.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $owner_id
 * @property-read User $owner
 */
class Team extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
```
