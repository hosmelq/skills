# Store Tests: Access Existing Record

Pest POST store: Guest and unrelated tenant on existing-record deactivation; two versus three bindings.

## Complete block — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Facility;

describe('store', function (): void {
    it('requires authentication', function (): void {
        $facility = Facility::factory()->createOne();

        $response = post(route('teams.facilities.deactivation.store', [
            'team' => $facility->team,
            'facility' => $facility,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('prevents deactivating from an unrelated tenant', function (): void {
        $unrelatedFacility = Facility::factory()->createOne();

        signIn();

        $response = post(route('teams.facilities.deactivation.store', [
            'team' => $unrelatedFacility->team,
            'facility' => $unrelatedFacility,
        ]));

        $response->assertForbidden();
    });
});
```

## Complete block — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Cabinet;

describe('store', function (): void {
    it('requires authentication', function (): void {
        $cabinet = Cabinet::factory()->createOne();

        $response = post(route('teams.members.cabinets.deactivation.store', [
            'team' => $cabinet->member->team,
            'member' => $cabinet->member,
            'cabinet' => $cabinet,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('prevents deactivating from an unrelated tenant', function (): void {
        $cabinet = Cabinet::factory()->createOne();

        signIn();

        $response = post(route('teams.members.cabinets.deactivation.store', [
            'team' => $cabinet->member->team,
            'member' => $cabinet->member,
            'cabinet' => $cabinet,
        ]));

        $response->assertForbidden();
    });
});
```
