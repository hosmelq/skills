# Models: Parent Relations

Add `belongsTo` relations to parent records, including roles that point to the same model through a differently named foreign key and optional parents.

- Name a role relation after the role the parent plays, which can be shorter than its key, and pass the foreign key whenever the method name differs from the related model's name, even when Laravel would infer it.
- Add each foreign key to the foreign key group and each relation to the relation group, alphabetically.
- Plain relations need no test; test a relation only when it adds conditions or attributes.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $attending_veterinarian_id
 * @property-read int $clinic_id
 * @property-read int $pet_id
 * @property-read null|int $referring_veterinarian_id
 * @property-read User $attendingVeterinarian
 * @property-read Clinic $clinic
 * @property-read Pet $pet
 * @property-read null|User $referringVeterinarian
 */
final class Visit extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function attendingVeterinarian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attending_veterinarian_id');
    }

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * @return BelongsTo<Pet, $this>
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function referringVeterinarian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referring_veterinarian_id');
    }
}
```
