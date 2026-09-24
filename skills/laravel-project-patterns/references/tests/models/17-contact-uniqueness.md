# Model Tests: Normalized Contact Uniqueness

Database uniqueness within a tenant for case-insensitive email and normalized phone numbers; each test asserts its specific unique index.

```php
<?php

declare(strict_types=1);

use App\Models\Member;
use Illuminate\Database\UniqueConstraintViolationException;

it('enforces case-insensitive email uniqueness per tenant at the database level', function (): void {
    $member = Member::factory()->createOne([
        'email' => 'member@example.com',
    ]);

    expect(fn () => Member::factory()->recycle($member->team)->createOne([
        'email' => 'MEMBER@example.com',
    ]))->toThrow(function (UniqueConstraintViolationException $exception): void {
        expect($exception->index)->toBe('members_active_email_unique');
    });
});

it('enforces normalized phone uniqueness per tenant at the database level', function (): void {
    $member = Member::factory()->createOne([
        'phone_number' => '+14155550110',
    ]);

    expect(fn () => Member::factory()->recycle($member->team)->createOne([
        'phone_number' => '+1 415 555 0110',
    ]))->toThrow(function (UniqueConstraintViolationException $exception): void {
        expect($exception->index)->toBe('members_active_phone_number_unique');
    });
});
```
