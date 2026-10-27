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
