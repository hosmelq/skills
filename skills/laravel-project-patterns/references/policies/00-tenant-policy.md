# Policies: Tenant Resource Policy

Authorize a tenant-owned resource by tenant membership: abilities without a record, such as `create` and `viewAny`, receive the tenant, and record abilities check the record's tenant.

- Controllers apply abilities with `can:` middleware, passing the tenant route parameter to abilities without a record, such as `can:create,App\Models\Client,clinic`.
- Test denials through the endpoints in the controller's feature test, inside the `describe` block of the action.

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Client;
use App\Models\Clinic;
use App\Models\User;

final class ClientPolicy
{
    public function create(User $user, Clinic $clinic): bool
    {
        return $user->belongsToClinic($clinic);
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->belongsToClinic($client->clinic);
    }

    public function update(User $user, Client $client): bool
    {
        return $user->belongsToClinic($client->clinic);
    }

    public function view(User $user, Client $client): bool
    {
        return $user->belongsToClinic($client->clinic);
    }

    public function viewAny(User $user, Clinic $clinic): bool
    {
        return $user->belongsToClinic($clinic);
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;
use function Pest\Laravel\post;

use App\Models\Client;

describe('store', function (): void {
    it('prevents creating from an unrelated clinic', function (): void {
        $unrelatedClient = Client::factory()->createOne();

        login();

        $response = post(route('clinics.clients.store', [
            'clinic' => $unrelatedClient->clinic,
        ]));

        $response->assertForbidden();
    });
});

describe('update', function (): void {
    it('prevents updating from an unrelated clinic', function (): void {
        $client = Client::factory()->createOne();

        login();

        $response = patch(route('clinics.clients.update', [
            'clinic' => $client->clinic,
            'client' => $client,
        ]));

        $response->assertForbidden();
    });
});
```
