# HTTP Resource Tests: API Resource Envelopes

Explicit API resource wrappers serialize models into exact data/attributes/id/type envelopes. One example maps an enum status; the other includes optional contact formatting.

These explicit `JsonApiResource` classes override `toId()` with `sqid` and `toType()` with the asserted type.

```php
<?php

declare(strict_types=1);

use App\Http\Resources\Api\EnrollmentResource;
use App\Models\Enrollment;

it('formats resource correctly', function (): void {
    $enrollment = Enrollment::factory()->createOne();

    $resource = json_decode(EnrollmentResource::make($enrollment)->toJson(), true);

    expect($resource)->toEqual([
        'data' => [
            'attributes' => [
                'status' => $enrollment->status->value,
            ],
            'id' => $enrollment->sqid,
            'type' => 'enrollments',
        ],
    ]);
});
```

Contact attributes in a different API resource:

```php
<?php

declare(strict_types=1);

use App\Http\Resources\Api\TeamResource;
use App\Models\Team;

it('formats resource correctly', function (): void {
    $team = Team::factory()->createOne();

    $resource = json_decode(TeamResource::make($team)->toJson(), true);

    expect($resource)->toEqual([
        'data' => [
            'attributes' => [
                'contact_email' => $team->contact_email,
                'contact_phone_number' => $team->contact_phone_number?->formatE164(),
                'name' => $team->name,
            ],
            'id' => $team->sqid,
            'type' => 'teams',
        ],
    ]);
});
```
