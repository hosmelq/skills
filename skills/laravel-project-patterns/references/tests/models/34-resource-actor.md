# Model Tests: Related Actor Resource

Exact resource JSON with a related member and a role-specific requesting actor, request timestamp, enum status, public ID and standard timestamps.

The requesting user is an explicit relation role; retain `for($user, 'requestedByUser')`. Resolve both expected nested resources independently.

```php
<?php

declare(strict_types=1);

use App\Models\Enrollment;
use App\Models\Member;
use App\Models\User;

it('formats resource correctly', function (): void {
    $member = Member::factory()->createOne();
    $requestedByUser = User::factory()->createOne();

    $enrollment = Enrollment::factory()
        ->recycle($member)
        ->for($requestedByUser, 'requestedByUser')
        ->createOne();

    $memberResource = json_decode($member->toResource()->toJson(), true);
    $requestedByUserResource = json_decode($requestedByUser->toResource()->toJson(), true);
    $resource = json_decode($enrollment->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'created_at' => $enrollment->created_at->toJSON(),
        'member' => $memberResource,
        'id' => $enrollment->public_id,
        'requested_at' => $enrollment->requested_at->toJSON(),
        'requested_by_user' => $requestedByUserResource,
        'status' => $enrollment->status->value,
        'updated_at' => $enrollment->updated_at->toJSON(),
    ]);
});
```
