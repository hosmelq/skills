# Models: Child Relations and Creation Defaults

Implement hasMany, role-specific inverse keys, hasOne and a filtered default relation. withAttributes with asConditions:false supplies creation defaults without adding a retrieval predicate.

The team attribute is a creation default, not tenant isolation or a database constraint. A filtered `hasOne` is distinct from an unfiltered one.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read int $team_id
 * @property-read EloquentCollection<int, MemberAddress> $addresses
 * @property-read null|MemberAddress $defaultAddress
 * @property-read null|Enrollment $enrollment
 * @property-read EloquentCollection<int, WorkOrder> $workOrders
 */
class Member extends Model
{
    /**
     * @return HasMany<MemberAddress, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(MemberAddress::class)
            ->withAttributes(['team_id' => $this->team_id], asConditions: false);
    }

    /**
     * @return HasOne<MemberAddress, $this>
     */
    public function defaultAddress(): HasOne
    {
        return $this->hasOne(MemberAddress::class)->where('is_default', true);
    }

    /**
     * @return HasOne<Enrollment, $this>
     */
    public function enrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class);
    }

    /**
     * @return HasMany<WorkOrder, $this>
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    /**
     * @return HasMany<WorkOrder, $this>
     */
    public function currentWorkOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'current_facility_id');
    }
}
```
