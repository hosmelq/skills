# Actions: Update Action

Update a record from a partial input object and audit the changed attributes.

- Fields omitted from the input stay unchanged; fields sent as `null` are cleared.
- Record an `AuditLogEvent` case, added alphabetically, inside the transaction, targeting the record and its tenant.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Clients\Inputs;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
final class UpdateClientInput extends Data
{
    public function __construct(
        public readonly null|Optional|string $email,
        public readonly null|Optional|string $firstName,
        public readonly null|Optional|string $lastName,
        public readonly null|Optional|string $phoneNumber,
    ) {}
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\Clients;

use function HosmelQ\AuditLog\audit_log;

use App\Actions\Clients\Inputs\UpdateClientInput;
use App\Enums\AuditLogEvent;
use App\Models\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class UpdateClient
{
    public function handle(Client $client, UpdateClientInput $input): Client
    {
        return DB::transaction(function () use ($client, $input): Client {
            $client->update($input->transform());

            /** @var array<string, null|bool|float|int|string> $before */
            $before = Arr::except($client->getPrevious(), 'updated_at');
            /** @var array<string, null|bool|float|int|string> $after */
            $after = Arr::except($client->getChanges(), 'updated_at');

            audit_log(AuditLogEvent::ClientUpdated)
                ->changes(before: $before, after: $after)
                ->target($client)
                ->tenant($client->clinic_id)
                ->record();

            return $client;
        });
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\Clients\Inputs\UpdateClientInput;
use App\Actions\Clients\UpdateClient;
use App\Models\Client;

it('updates only provided client fields', function (): void {
    $client = Client::factory()->createOne([
        'email' => 'john@gmail.com',
        'first_name' => 'John',
    ]);

    resolve(UpdateClient::class)->handle(
        $client,
        UpdateClientInput::from([
            'first_name' => 'Jane',
        ]),
    );

    assertDatabaseHas(Client::class, [
        'id' => $client->id,
        'email' => 'john@gmail.com',
        'first_name' => 'Jane',
    ]);
});

it('clears nullable client fields', function (): void {
    $client = Client::factory()->createOne([
        'email' => 'john@gmail.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    resolve(UpdateClient::class)->handle(
        $client,
        UpdateClientInput::from([
            'email' => null,
            'last_name' => null,
        ]),
    );

    assertDatabaseHas(Client::class, [
        'id' => $client->id,
        'email' => null,
        'first_name' => 'John',
        'last_name' => null,
    ]);
});
```
