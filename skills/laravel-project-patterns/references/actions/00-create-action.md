# Actions: Create Action

Create a tenant-owned record from a validated input object through a final readonly action with a single `handle()` method, called from controllers and other actions through the container.

- Group actions by resource in `App\Actions\<Resources>` and their inputs in `Inputs`.
- Inputs extend Spatie Data with snake_case mapping; optional fields are `Optional` so `transform()` returns only the fields sent.
- Record an `AuditLogEvent` case, added alphabetically, inside the transaction, targeting the record and its tenant.
- Test actions in the integration suite through `resolve()`.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Clients\Inputs;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
final class CreateClientInput extends Data
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

use App\Actions\Clients\Inputs\CreateClientInput;
use App\Enums\AuditLogEvent;
use App\Models\Client;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;

final readonly class CreateClient
{
    public function handle(Clinic $clinic, CreateClientInput $input): Client
    {
        return DB::transaction(function () use ($clinic, $input): Client {
            $client = $clinic->clients()->create($input->transform());

            audit_log(AuditLogEvent::ClientCreated)
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

use App\Actions\Clients\CreateClient;
use App\Actions\Clients\Inputs\CreateClientInput;
use App\Models\Client;
use App\Models\Clinic;

it('creates a client', function (): void {
    $clinic = Clinic::factory()->createOne();
    $client = resolve(CreateClient::class)->handle(
        $clinic,
        CreateClientInput::from([
            'email' => 'john@gmail.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone_number' => '+505 8888 8888',
        ]),
    );

    assertDatabaseHas(Client::class, [
        'id' => $client->id,
        'clinic_id' => $clinic->id,
        'email' => 'john@gmail.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone_number' => '+50588888888',
    ]);
});
```
