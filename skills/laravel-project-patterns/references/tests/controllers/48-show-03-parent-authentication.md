# Show Tests: One-Parent Record Authentication

Browser GET show one-parent record authentication: a guest redirects to login and an unrelated authenticated tenant gets 403. Keep every valid route parameter for this route depth.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\MemberAddress;

describe('show', function (): void {
    it('requires authentication', function (): void {
        $address = MemberAddress::factory()->createOne();

        $response = get(route('teams.members.addresses.show', [
            'team' => $address->member->team,
            'member' => $address->member,
            'address' => $address,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('prevents viewing from an unrelated tenant', function (): void {
        $address = MemberAddress::factory()->createOne();

        login();

        $response = get(route('teams.members.addresses.show', [
            'team' => $address->member->team,
            'member' => $address->member,
            'address' => $address,
        ]));

        $response->assertForbidden();
    });
});
```
