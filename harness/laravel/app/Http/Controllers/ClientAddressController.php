<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientAddressRequest;
use App\Models\Client;
use App\Models\Clinic;
use Illuminate\Http\RedirectResponse;

final class ClientAddressController
{
    public function store(StoreClientAddressRequest $request, Clinic $clinic, Client $client): RedirectResponse
    {
        $client->addresses()->create($request->validated());

        return back();
    }
}
