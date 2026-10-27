# Controllers: Singleton Controller

Model a state change of a record, such as deactivation, as a creatable singleton sub-resource whose `store` applies the state and `destroy` reverts it.

- Register it with `Route::singleton(...)->creatable()->only(['destroy', 'store'])`.
- Authorize each method with its lifecycle ability.

```php
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
use Inertia\Inertia;

final class RoomDeactivationController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:deactivate,room', only: ['store']),
            new Middleware('can:reactivate,room', only: ['destroy']),
        ];
    }

    public function destroy(
        Clinic $clinic,
        Room $room,
        ReactivateRoom $reactivateRoom,
    ): RedirectResponse {
        $reactivateRoom->handle($room);

        return Inertia::flash('toast', [
            'title' => trans()->string('room.reactivated.title'),
        ])->back();
    }

    public function store(
        Clinic $clinic,
        Room $room,
        DeactivateRoom $deactivateRoom,
    ): RedirectResponse {
        $deactivateRoom->handle($room);

        return Inertia::flash('toast', [
            'title' => trans()->string('room.deactivated.title'),
        ])->back();
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\delete;
use function Pest\Laravel\post;

use App\Models\Room;

describe('destroy', function (): void {
    it('reactivates a room', function (): void {
        $room = Room::factory()->deactivated()->createOne();

        login(clinic: $room->clinic);

        $response = delete(route('clinics.rooms.deactivation.destroy', [
            'clinic' => $room->clinic,
            'room' => $room,
        ]));

        $response->assertRedirect()->assertInertiaFlash('toast.title', 'Room reactivated');

        assertDatabaseHas(Room::class, [
            'id' => $room->id,
            'deactivated_at' => null,
        ]);
    });
});

describe('store', function (): void {
    it('deactivates a room', function (): void {
        $room = Room::factory()->createOne();

        login(clinic: $room->clinic);

        $response = post(route('clinics.rooms.deactivation.store', [
            'clinic' => $room->clinic,
            'room' => $room,
        ]));

        $response->assertRedirect()->assertInertiaFlash('toast.title', 'Room deactivated');

        assertDatabaseHas(Room::class, [
            'id' => $room->id,
            'deactivated_at' => now(),
        ]);
    });
});
```
