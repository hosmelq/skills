# Models: Child Relations

Add `hasMany` and `hasOne` relations to child records, including children that inherit the tenant key when created through the relation, children found through a role column and a single child selected by a condition.

- When the application creates children through this relation and they also store the tenant key, chain `withAttributes([...], asConditions: false)` so they inherit it without filtering queries.
- Pass the foreign key when the child references this model through a role column, as in `hasMany(Visit::class, 'attending_veterinarian_id')`.
- A conditional `hasOne` adds its condition to the relation.
- Plain relations, including inherited attributes and a `hasOne` for a child the parent has at most one of, need no test; test a conditional relation in the integration suite.
- Use the collection name the model already imports in `@property-read`: `EloquentCollection` when the model also imports `Illuminate\Support\Collection`.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read int $id
 * @property-read int $clinic_id
 * @property-read EloquentCollection<int, ClientAddress> $addresses
 * @property-read null|ClientAddress $defaultAddress
 * @property-read EloquentCollection<int, Pet> $pets
 */
final class Client extends Model
{
    /**
     * @return HasMany<ClientAddress, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(ClientAddress::class)
            ->withAttributes(['clinic_id' => $this->clinic_id], asConditions: false);
    }

    /**
     * @return HasOne<ClientAddress, $this>
     */
    public function defaultAddress(): HasOne
    {
        return $this->hasOne(ClientAddress::class)->where('is_default', true);
    }

    /**
     * @return HasMany<Pet, $this>
     */
    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class)
            ->withAttributes(['clinic_id' => $this->clinic_id], asConditions: false);
    }
}
```

```php
<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\ClientAddress;

it('loads only the persisted default address through the default address scope', function (): void {
    $client = Client::factory()->createOne();
    $nonDefaultAddress = ClientAddress::factory()->for($client)->createOne(['is_default' => false]);
    $defaultAddress = ClientAddress::factory()->for($client)->createOne(['is_default' => true]);

    $client->load('defaultAddress');

    expect($client->defaultAddress)
        ->not->toBeNull()
        ->is($defaultAddress)->toBeTrue()
        ->is($nonDefaultAddress)->toBeFalse()
        ->is_default->toBeTrue();
});
```
