<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Rooms\DeactivateRoom;
use App\Actions\Rooms\ReactivateRoom;
use App\Models\Clinic;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final class RoomDeactivationController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:deactivate,room', only: ['store']),
            new Middleware('can:reactivate,room', only: ['destroy']),
        ];
    }

    public function destroy(Clinic $clinic, Room $room, ReactivateRoom $reactivateRoom): RedirectResponse
    {
        $reactivateRoom->handle($room);

        return back();
    }

    public function store(Clinic $clinic, Room $room, DeactivateRoom $deactivateRoom): RedirectResponse
    {
        $deactivateRoom->handle($room);

        return back();
    }
}
