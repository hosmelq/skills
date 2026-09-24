# Model Tests: Contact Resource Fields

Exact model resource JSON for names, display value, optional email/note/phone, public ID and immutable timestamps.

```php
<?php

declare(strict_types=1);

use App\Models\Member;

it('formats resource correctly', function (): void {
    $member = Member::factory()->createOne();

    $resource = json_decode($member->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'created_at' => $member->created_at->toJSON(),
        'display_name' => $member->display_name,
        'email' => $member->email,
        'first_name' => $member->first_name,
        'id' => $member->public_id,
        'last_name' => $member->last_name,
        'note' => $member->note,
        'phone_number' => $member->phone_number?->formatE164(),
        'updated_at' => $member->updated_at->toJSON(),
    ]);
});
```
