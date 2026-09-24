# Store Tests: Access Unrelated Root

POST store: Unrelated tenant; tenant-only fixture versus fixture-derived tenant and distinct valid payloads.

## Tenant-only request

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Team;

describe('store', function (): void {
    it('prevents storing from an unrelated tenant', function (): void {
        $unrelatedTeam = Team::factory()->createOne();

        login();

        $response = post(route('teams.facilities.store', [
            'team' => $unrelatedTeam,
        ]));

        $response->assertForbidden();
    });
});
```

## Tenant derived from a record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Member;

describe('store', function (): void {
    it('prevents storing from an unrelated tenant', function (): void {
        $unrelatedMember = Member::factory()->createOne();

        login();

        $response = post(route('teams.members.store', [
            'team' => $unrelatedMember->team,
        ]));

        $response->assertForbidden();
    });
});
```

## Required name payload

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Team;

describe('store', function (): void {
    it('prevents storing from an unrelated tenant', function (): void {
        $team = Team::factory()->createOne();

        login();

        $response = post(route('teams.item-groups.store', [
            'team' => $team,
        ]), [
            'name' => 'Electronics',
        ]);

        $response->assertForbidden();
    });
});
```

## Required enum and name payload

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Enums\WorkOrderBaseStatus;
use App\Models\Team;

describe('store', function (): void {
    it('prevents storing from an unrelated tenant', function (): void {
        $team = Team::factory()->createOne();

        login();

        $response = post(route('teams.work-order-statuses.store', [
            'team' => $team,
        ]), [
            'base_status' => WorkOrderBaseStatus::Received(),
            'name' => 'Received',
        ]);

        $response->assertForbidden();
    });
});
```
