# Store Tests: Relations Owner And Assignment

Pest POST store: Selected owner foreign/deleted; selected assignment foreign/inactive/deleted. Every distinct error field remains.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Cabinet;
use App\Models\Member;
use App\Models\Team;

describe('store', function (): void {
    it('rejects a newly assigned relation from another tenant: member_id', function (): void {
        $team = Team::factory()->createOne();
        $member = Member::factory()->createOne();

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'member_id' => $member->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'member_id' => 'The selected member id is invalid.',
        ]);
    });

    it('rejects a newly assigned relation from another tenant: cabinet_id', function (): void {
        $team = Team::factory()->createOne();
        $cabinet = Cabinet::factory()->createOne();

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'cabinet_id' => $cabinet->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'cabinet_id' => 'The selected cabinet id is invalid.',
        ]);
    });

    it('rejects a newly assigned inactive relation: cabinet_id', function (): void {
        $team = Team::factory()->createOne();
        $cabinet = Cabinet::factory()->deactivated()->recycle($team)->createOne();

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'cabinet_id' => $cabinet->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'cabinet_id' => 'The selected cabinet id is invalid.',
        ]);
    });

    it('rejects a newly assigned soft deleted relation: member_id', function (): void {
        $team = Team::factory()->createOne();
        $member = Member::factory()->trashed()->for($team)->createOne();

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'member_id' => $member->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'member_id' => 'The selected member id is invalid.',
        ]);
    });

    it('rejects a newly assigned soft deleted relation: cabinet_id', function (): void {
        $team = Team::factory()->createOne();
        $cabinet = Cabinet::factory()->trashed()->recycle($team)->createOne();

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'cabinet_id' => $cabinet->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'cabinet_id' => 'The selected cabinet id is invalid.',
        ]);
    });
});
```
