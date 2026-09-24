# Show Tests: Direct Record Authentication

Browser GET show direct record authentication: a guest redirects to login and an unrelated authenticated tenant gets 403. Keep every valid route parameter for this route depth.

## Authentication And Access

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Facility;

describe('show', function (): void {
    it('requires authentication', function (): void {
        $facility = Facility::factory()->createOne();

        $response = get(route('teams.facilities.show', [
            'team' => $facility->team,
            'facility' => $facility,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('prevents viewing from an unrelated tenant', function (): void {
        $unrelatedFacility = Facility::factory()->createOne();

        login();

        $response = get(route('teams.facilities.show', [
            'team' => $unrelatedFacility->team,
            'facility' => $unrelatedFacility,
        ]));

        $response->assertForbidden();
    });
});
```
