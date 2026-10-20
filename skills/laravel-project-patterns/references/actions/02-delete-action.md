# Actions: Delete Action

Delete a record and the children that only exist for it, refusing when it has history that must be kept.

- Guard with `throw_if()` and a domain exception in `App\Exceptions`; include soft deleted history.
- Record an `AuditLogEvent` case, added alphabetically, inside the transaction, targeting the record and its tenant.

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class CannotDeleteClientWithPets extends Exception
{
    public function __construct()
    {
        parent::__construct('Cannot delete a client with pets.');
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\Clients;

use function HosmelQ\AuditLog\audit_log;

use App\Enums\AuditLogEvent;
use App\Exceptions\CannotDeleteClientWithPets;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

final readonly class DeleteClient
{
    public function handle(Client $client): void
    {
        DB::transaction(function () use ($client): void {
            throw_if($client->pets()->withTrashed()->exists(), CannotDeleteClientWithPets::class);

            $client->addresses()->delete();
            $client->delete();

            audit_log(AuditLogEvent::ClientDeleted)
                ->target($client)
                ->tenant($client->clinic_id)
                ->record();
        });
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;

use App\Actions\Clients\DeleteClient;
use App\Exceptions\CannotDeleteClientWithPets;
use App\Models\ClientAddress;
use App\Models\Pet;

it('rejects deleting a client with soft deleted pets', function (): void {
    $pet = Pet::factory()->trashed()->createOne();

    expect(fn () => resolve(DeleteClient::class)->handle($pet->client))
        ->toThrow(CannotDeleteClientWithPets::class, 'Cannot delete a client with pets.');

    assertNotSoftDeleted($pet->client);
});

it('deletes a client and its addresses', function (): void {
    $address = ClientAddress::factory()->createOne();

    resolve(DeleteClient::class)->handle($address->client);

    assertSoftDeleted($address);
    assertSoftDeleted($address->client);
});
```
