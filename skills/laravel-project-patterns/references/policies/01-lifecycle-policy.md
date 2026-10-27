# Policies: Lifecycle Policy

Deny abilities that do not apply to a record's lifecycle state, such as editing or deactivating a deactivated record and reactivating an active one, so their endpoints respond 403.

- Combine tenant membership with the model's state predicates.
- Test denials through the endpoints in the controller's feature test, inside the `describe` block of the action.

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Clinic;
use App\Models\Room;
use App\Models\User;

final class RoomPolicy
{
    public function create(User $user, Clinic $clinic): bool
    {
        return $user->belongsToClinic($clinic);
    }

    public function deactivate(User $user, Room $room): bool
    {
        return $user->belongsToClinic($room->clinic)
            && $room->isActive();
    }

    public function delete(User $user, Room $room): bool
    {
        return $user->belongsToClinic($room->clinic)
            && $room->isActive();
    }

    public function reactivate(User $user, Room $room): bool
    {
        return $user->belongsToClinic($room->clinic)
            && $room->isDeactivated();
    }

    public function update(User $user, Room $room): bool
    {
        return $user->belongsToClinic($room->clinic)
            && $room->isActive();
    }

    public function view(User $user, Room $room): bool
    {
        return $user->belongsToClinic($room->clinic);
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

use function Pest\Laravel\delete;
use function Pest\Laravel\post;

use App\Models\Room;

describe('store', function (): void {
    it('prevents deactivating a room that is already deactivated', function (): void {
        $room = Room::factory()->deactivated()->createOne();

        login(clinic: $room->clinic);

        $response = post(route('clinics.rooms.deactivation.store', [
            'clinic' => $room->clinic,
            'room' => $room,
        ]));

        $response->assertForbidden();
    });
});

describe('destroy', function (): void {
    it('prevents reactivating an active room', function (): void {
        $room = Room::factory()->createOne();

        login(clinic: $room->clinic);

        $response = delete(route('clinics.rooms.deactivation.destroy', [
            'clinic' => $room->clinic,
            'room' => $room,
        ]));

        $response->assertForbidden();
    });
});
```
