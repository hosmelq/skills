# Model Tests: Display Name Precedence

In-memory display-name accessor: the supplied name takes precedence over email, and email over a normalized phone number.

```php
<?php

declare(strict_types=1);

use App\Models\Member;

it('builds the display name from the first available value', function (): void {
    $emailMember = new Member([
        'email' => 'john@example.com',
        'phone_number' => '+1 415 555 0110',
    ]);

    $namedMember = new Member([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone_number' => '+1 415 555 0110',
    ]);

    $phoneMember = new Member([
        'phone_number' => '+1 415 555 0110',
    ]);

    expect($namedMember->display_name)->toBe('John Doe')
        ->and($emailMember->display_name)->toBe('john@example.com')
        ->and($phoneMember->display_name)->toBe('+14155550110');
});
```
