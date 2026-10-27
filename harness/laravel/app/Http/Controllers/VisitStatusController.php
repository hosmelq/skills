<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateVisitStatusRequest;
use App\Models\Clinic;
use App\Models\VisitStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final class VisitStatusController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:update,visit_status', only: ['update']),
        ];
    }

    public function update(UpdateVisitStatusRequest $request, Clinic $clinic, VisitStatus $visitStatus): RedirectResponse
    {
        $visitStatus->update($request->validated());

        return back();
    }
}
