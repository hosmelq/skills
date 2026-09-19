# Destroy Tests: Reactivating A Deactivated Record

DELETE of a deactivation subresource reactivates its entity. Complete inactive-fixture authentication, tenant authorization and binding cases, followed by identity-matched reactivation and toast. Distinguish an exact collection redirect from an endpoint whose test asserts redirect status only.

Use reactivates the record in either success example. There is no separate deactivation model fixture. Actions are mocked; persisted reactivation is not asserted. Do not strengthen redirect-only assertions to an exact back URL.

## Exact Collection Redirect

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\Facilities\ReactivateFacility;
use App\Models\Facility;
use App\Models\Team;

describe('destroy', function (): void {
    it('requires authentication', function (): void {
        $facility = Facility::factory()->deactivated()->createOne();

        $response = delete(route('teams.facilities.deactivation.destroy', [
            'team' => $facility->team,
            'facility' => $facility,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('prevents reactivating from an unrelated tenant', function (): void {
        $unrelatedFacility = Facility::factory()->deactivated()->createOne();

        signIn();

        $response = delete(route('teams.facilities.deactivation.destroy', [
            'team' => $unrelatedFacility->team,
            'facility' => $unrelatedFacility,
        ]));

        $response->assertForbidden();
    });

    it('returns not found when the record belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();

        $unrelatedFacility = Facility::factory()->deactivated()->createOne();

        signIn(team: $team);

        $response = delete(route('teams.facilities.deactivation.destroy', [
            'team' => $team,
            'facility' => $unrelatedFacility,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record is soft deleted', function (): void {
        $facility = Facility::factory()->deactivated()->trashed()->createOne();

        signIn(team: $facility->team);

        $response = delete(route('teams.facilities.deactivation.destroy', [
            'team' => $facility->team,
            'facility' => $facility,
        ]));

        $response->assertNotFound();
    });

    it('reactivates the record', function (): void {
        $facility = Facility::factory()->deactivated()->createOne();

        signIn(team: $facility->team);

        mock(ReactivateFacility::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Facility $facilityArgument): bool => $facilityArgument->is($facility));

        $response = delete(route('teams.facilities.deactivation.destroy', [
            'team' => $facility->team,
            'facility' => $facility,
        ]));

        $response->assertRedirectToRoute('teams.facilities.index', [
            'team' => $facility->team,
        ])
            ->assertToast('Facility reactivated');
    });
});
```

## Nested Redirect Status Only

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\Cabinets\ReactivateCabinet;
use App\Models\Cabinet;

describe('destroy', function (): void {
    it('reactivates the record', function (): void {
        $cabinet = Cabinet::factory()->deactivated()->createOne();

        signIn(team: $cabinet->member->team);

        mock(ReactivateCabinet::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Cabinet $cabinetArgument): bool => $cabinetArgument->is($cabinet));

        $response = delete(route('teams.members.cabinets.deactivation.destroy', [
            'team' => $cabinet->member->team,
            'member' => $cabinet->member,
            'cabinet' => $cabinet,
        ]));

        $response->assertRedirect()
            ->assertToast('Cabinet reactivated');
    });
});
```
