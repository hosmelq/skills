<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Clients\CreateClient;
use App\Actions\Clients\Inputs\CreateClientInput;
use App\Actions\Clients\Inputs\UpdateClientInput;
use App\Actions\Clients\UpdateClient;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\Clinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final class ClientController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:create,'.Client::class.',clinic', only: ['store']),
            new Middleware('can:update,client', only: ['update']),
        ];
    }

    public function store(StoreClientRequest $request, Clinic $clinic, CreateClient $createClient): RedirectResponse
    {
        $createClient->handle($clinic, CreateClientInput::from($request->validated()));

        return back();
    }

    public function update(
        UpdateClientRequest $request,
        Clinic $clinic,
        Client $client,
        UpdateClient $updateClient,
    ): RedirectResponse {
        $updateClient->handle($client, UpdateClientInput::from($request->validated()));

        return back();
    }
}
