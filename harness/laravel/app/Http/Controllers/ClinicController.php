<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final class ClinicController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:update,clinic', only: ['update']),
        ];
    }

    public function update(Request $request, Clinic $clinic): RedirectResponse
    {
        $clinic->update($request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
        ]));

        return back();
    }
}
