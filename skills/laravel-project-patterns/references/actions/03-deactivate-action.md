# Actions: Deactivate Action

Change a record's lifecycle state through its model method and audit the changed column; reactivation mirrors it.

- Record an `AuditLogEvent` case, added alphabetically, inside the transaction, targeting the record and its tenant.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Rooms;

use function HosmelQ\AuditLog\audit_log;

use App\Enums\AuditLogEvent;
use App\Models\Room;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class DeactivateRoom
{
    public function handle(Room $room): void
    {
        DB::transaction(function () use ($room): void {
            $room->deactivate();

            /** @var array<string, null|bool|float|int|string> $before */
            $before = Arr::only($room->getPrevious(), 'deactivated_at');
            /** @var array<string, null|bool|float|int|string> $after */
            $after = Arr::only($room->getChanges(), 'deactivated_at');

            audit_log(AuditLogEvent::RoomDeactivated)
                ->changes(before: $before, after: $after)
                ->target($room)
                ->tenant($room->clinic_id)
                ->record();
        });
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\Rooms\DeactivateRoom;
use App\Models\Room;

it('deactivates a room', function (): void {
    $room = Room::factory()->createOne();

    resolve(DeactivateRoom::class)->handle($room);

    assertDatabaseHas(Room::class, [
        'id' => $room->id,
        'deactivated_at' => now(),
    ]);
});
```
