# Controllers: Resource Controller

Serve a tenant-owned resource with an Inertia resource controller: pages receive resources as props, writes validate with form requests, run actions and redirect with a flashed toast.

- Authorize every method with `can:` middleware in `middleware()`.
- Pass `$request->validated()` to the action's input with `Input::from()`.
- Turn domain exceptions into `ValidationException` messages.
- Flash toasts with `Inertia::flash()` and assert them with `assertInertiaFlash()`.
- Feature tests group cases in one `describe` block per method and run the real actions, which are final; assert audit events with `AuditLog::fake()`.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Clients\CreateClient;
use App\Actions\Clients\DeleteClient;
use App\Actions\Clients\Inputs\CreateClientInput;
use App\Actions\Clients\Inputs\UpdateClientInput;
use App\Actions\Clients\UpdateClient;
use App\Exceptions\CannotDeleteClientWithPets;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\Clinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class ClientController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:create,'.Client::class.',clinic', only: ['create', 'store']),
            new Middleware('can:delete,client', only: ['destroy']),
            new Middleware('can:update,client', only: ['edit', 'update']),
            new Middleware('can:view,client', only: ['show']),
            new Middleware('can:viewAny,'.Client::class.',clinic', only: ['index']),
        ];
    }

    public function create(Clinic $clinic): Response
    {
        return Inertia::render('clients/Create', [
            'clinic' => $clinic->toResource(),
        ]);
    }

    public function destroy(
        Clinic $clinic,
        Client $client,
        DeleteClient $deleteClient,
    ): RedirectResponse {
        try {
            $deleteClient->handle($client);
        } catch (CannotDeleteClientWithPets) {
            throw ValidationException::withMessages([
                'client' => trans()->string('client.validation.has_pets'),
            ]);
        }

        Inertia::flash('toast', ['title' => trans()->string('client.deleted.title')]);

        return to_route('clinics.clients.index', [
            'clinic' => $clinic,
        ]);
    }

    public function edit(Clinic $clinic, Client $client): Response
    {
        return Inertia::render('clients/Edit', [
            'client' => $client->toResource(),
            'clinic' => $clinic->toResource(),
        ]);
    }

    public function index(Clinic $clinic): Response
    {
        $clients = $clinic->clients()->latest('id')->paginate();

        return Inertia::render('clients/Index', [
            'clients' => $clients->toResourceCollection(),
            'clinic' => $clinic->toResource(),
        ]);
    }

    public function show(Clinic $clinic, Client $client): Response
    {
        return Inertia::render('clients/Show', [
            'client' => $client->toResource(),
            'clinic' => $clinic->toResource(),
            'defaultAddress' => $client->defaultAddress?->toResource(),
        ]);
    }

    public function store(
        StoreClientRequest $request,
        Clinic $clinic,
        CreateClient $createClient,
    ): RedirectResponse {
        $client = $createClient->handle($clinic, CreateClientInput::from($request->validated()));

        Inertia::flash('toast', ['title' => trans()->string('client.created.title')]);

        return to_route('clinics.clients.show', [
            'clinic' => $clinic,
            'client' => $client,
        ]);
    }

    public function update(
        UpdateClientRequest $request,
        Clinic $clinic,
        Client $client,
        UpdateClient $updateClient,
    ): RedirectResponse {
        $updateClient->handle($client, UpdateClientInput::from($request->validated()));

        Inertia::flash('toast', ['title' => trans()->string('client.updated.title')]);

        return to_route('clinics.clients.show', [
            'clinic' => $clinic,
            'client' => $client,
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

use App\Enums\AuditLogEvent;
use App\Models\Client;
use App\Models\Clinic;
use App\Models\Pet;
use HosmelQ\AuditLog\Data\AuditLogData;
use HosmelQ\AuditLog\Facades\AuditLog;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('requires authentication', function (): void {
        $clinic = Clinic::factory()->createOne();
        $response = get(route('clinics.clients.create', [
            'clinic' => $clinic,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('shows the create client page', function (): void {
        $clinic = Clinic::factory()->createOne();

        login(clinic: $clinic);

        $response = get(route('clinics.clients.create', [
            'clinic' => $clinic,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($clinic): void {
                $page->component('clients/Create')->where('clinic.id', $clinic->sqid);
            });
    });
});

describe('destroy', function (): void {
    it('prevents deleting a client with pets', function (): void {
        $pet = Pet::factory()->createOne();

        login(clinic: $pet->clinic);

        $response = delete(route('clinics.clients.destroy', [
            'clinic' => $pet->clinic,
            'client' => $pet->client,
        ]));

        $response->assertRedirectBackWithErrors([
            'client' => 'This client has pets and cannot be deleted.',
        ]);
    });

    it('deletes a client', function (): void {
        $client = Client::factory()->createOne();

        login(clinic: $client->clinic);

        $response = delete(route('clinics.clients.destroy', [
            'clinic' => $client->clinic,
            'client' => $client,
        ]));

        $response->assertRedirectToRoute('clinics.clients.index', [
            'clinic' => $client->clinic,
        ])
            ->assertInertiaFlash('toast.title', 'Client deleted');

        assertSoftDeleted($client);
    });
});

describe('edit', function (): void {
    it('shows the edit client page', function (): void {
        $client = Client::factory()->createOne();

        login(clinic: $client->clinic);

        $response = get(route('clinics.clients.edit', [
            'clinic' => $client->clinic,
            'client' => $client,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($client): void {
                $page->component('clients/Edit')
                    ->where('client.id', $client->sqid)
                    ->where('clinic.id', $client->clinic->sqid);
            });
    });
});

describe('index', function (): void {
    it('lists clients', function (): void {
        $client = Client::factory()->createOne();

        Client::factory()->createOne();

        login(clinic: $client->clinic);

        $response = get(route('clinics.clients.index', [
            'clinic' => $client->clinic,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($client): void {
                $page->component('clients/Index')
                    ->has('clients.data', 1)
                    ->where('clients.data.0.id', $client->sqid)
                    ->where('clinic.id', $client->clinic->sqid);
            });
    });
});

describe('show', function (): void {
    it('returns not found when client belongs to different clinic', function (): void {
        $clinic = Clinic::factory()->createOne();
        $client = Client::factory()->createOne();

        login(clinic: $clinic);

        $response = get(route('clinics.clients.show', [
            'clinic' => $clinic,
            'client' => $client,
        ]));

        $response->assertNotFound();
    });

    it('shows a client', function (): void {
        $client = Client::factory()->withDefaultAddress()->createOne();

        login(clinic: $client->clinic);

        $response = get(route('clinics.clients.show', [
            'clinic' => $client->clinic,
            'client' => $client,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($client): void {
                $page->component('clients/Show')
                    ->where('client.id', $client->sqid)
                    ->where('clinic.id', $client->clinic->sqid)
                    ->where('defaultAddress.id', $client->defaultAddress->sqid);
            });
    });
});

describe('store', function (): void {
    it('creates a client', function (): void {
        $clinic = Clinic::factory()->createOne();

        login(clinic: $clinic);

        $response = post(route('clinics.clients.store', [
            'clinic' => $clinic,
        ]), [
            'first_name' => 'John',
        ]);
        $client = $clinic->clients()->sole();

        $response->assertRedirectToRoute('clinics.clients.show', [
            'clinic' => $clinic,
            'client' => $client,
        ])
            ->assertInertiaFlash('toast.title', 'Client created');

        expect($client->first_name)->toBe('John');
    });

    it('records client.created', function (): void {
        AuditLog::fake();

        $clinic = Clinic::factory()->createOne();

        login(clinic: $clinic);

        $response = post(route('clinics.clients.store', [
            'clinic' => $clinic,
        ]), [
            'first_name' => 'John',
        ]);

        $response->assertRedirect();

        $client = $clinic->clients()->sole();

        AuditLog::assertRecorded(
            fn (AuditLogData $log): bool => $log->event === AuditLogEvent::ClientCreated->value
                && $log->tenantId === (string) $client->clinic_id
                && $log->targets[0]->id === (string) $client->id,
        );
    });
});

describe('update', function (): void {
    it('updates a client', function (): void {
        $client = Client::factory()->createOne();

        login(clinic: $client->clinic);

        $response = patch(route('clinics.clients.update', [
            'clinic' => $client->clinic,
            'client' => $client,
        ]), [
            'first_name' => 'Jane',
        ]);

        $response->assertRedirectToRoute('clinics.clients.show', [
            'clinic' => $client->clinic,
            'client' => $client,
        ])
            ->assertInertiaFlash('toast.title', 'Client updated');

        expect($client->refresh()->first_name)->toBe('Jane');
    });
});
```
