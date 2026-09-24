# Store Tests: Access Unrelated Root

Pest POST store: Unrelated tenant; tenant-only fixture versus fixture-derived tenant and distinct valid payloads.

## Prevents storing from an unrelated tenant — variant 1

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

## Prevents storing from an unrelated tenant — variant 2

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

## Prevents storing from an unrelated tenant — variant 3

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

## Prevents storing from an unrelated tenant — variant 4

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
