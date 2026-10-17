# Models: New Model

Create an Eloquent model for a tenant-owned table, with Sqids, soft deletion, audit log identity, its parent and child relations and its unit tests. Keep only the members the inspected migration and lifecycle support.

- Document each column, accessor and relation with `@property-read`, in this group order: `id`, foreign keys, other columns, timestamps (`created_at`, `updated_at`, `deleted_at`), attributes without a column (accessors and virtual casts), relations. Sort each group alphabetically and mark nullable columns and optional relations `null|`.
- Dates are `CarbonImmutable` because the application calls `Date::use(CarbonImmutable::class)`, and models are unguarded.
- Models people manage implement `HasAuditLogIdentity`; the name is the attribute or accessor a person recognizes the record by, and records identified only by their id omit it. Register the model's snake_case alias, alphabetically, in the enforced morph map. Records the application only reads or keeps internally skip audit identity and Sqids.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasSqid;
use Carbon\CarbonImmutable;
use Database\Factories\PetFactory;
use HosmelQ\AuditLog\Contracts\HasAuditLogIdentity;
use HosmelQ\AuditLog\Support\AuditLogIdentity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property-read int $id
 * @property-read int $client_id
 * @property-read int $clinic_id
 * @property-read string $name
 * @property-read null|CarbonImmutable $registered_at
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read null|CarbonImmutable $deleted_at
 * @property-read Client $client
 * @property-read Clinic $clinic
 * @property-read Collection<int, Visit> $visits
 */
final class Pet extends Model implements HasAuditLogIdentity
{
    /** @use HasFactory<PetFactory> */
    use HasFactory;

    use HasSqid;
    use SoftDeletes;

    public function auditLogIdentity(): AuditLogIdentity
    {
        return new AuditLogIdentity(
            id: $this->id,
            type: $this->getMorphClass(),
            name: $this->name,
        );
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * @return HasMany<Visit, $this>
     */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
        ];
    }
}
```

Unit tests assert every trait except `HasFactory` in one test; casts and their test follow the casts reference.

```php
<?php

declare(strict_types=1);

use App\Models\Concerns\HasSqid;
use App\Models\Pet;
use Illuminate\Database\Eloquent\SoftDeletes;

it('uses traits', function (): void {
    $traits = class_uses_recursive(Pet::class);

    expect($traits)->toContain(HasSqid::class, SoftDeletes::class);
});

it('identifies pets in audit logs', function (): void {
    $pet = new Pet(['id' => 123, 'name' => 'Luna']);
    $identity = $pet->auditLogIdentity();

    expect($identity)->id->toBe('123')->name->toBe('Luna')->type->toBe('pet');
});
```
