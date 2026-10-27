<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\VisitStatuses\MoveVisitStatus;
use App\Http\Requests\MoveVisitStatusRequest;
use App\Models\Clinic;
use App\Models\VisitStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

final class MoveVisitStatusController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:update,visit_status'),
            new Middleware('sqids:move_after_id'),
        ];
    }

    public function __invoke(
        MoveVisitStatusRequest $request,
        Clinic $clinic,
        VisitStatus $visitStatus,
        MoveVisitStatus $moveVisitStatus,
    ): RedirectResponse {
        /** @var array{move_after_id?: null|int} $validated */
        $validated = $request->validated();
        $moveAfterVisitStatus = null;

        if (($validated['move_after_id'] ?? null) !== null) {
            $moveAfterVisitStatus = $clinic->visitStatuses()
                ->where('stage', $visitStatus->stage)
                ->whereKey($validated['move_after_id'])
                ->firstOrFail();
        }

        $moveVisitStatus->handle($visitStatus, $moveAfterVisitStatus);

        return Inertia::flash('toast', [
            'title' => trans()->string('visit_status.moved.title'),
        ])->back();
    }
}
