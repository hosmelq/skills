# Action Tests: Create Owner Guards

Integration action tests: Reject an owner that does not own the selected assignment, or an owner from another tenant or soft-deleted owner.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseCount;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Exceptions\WorkOrders\MemberDoesNotOwnCabinet;
use App\Exceptions\WorkOrders\MemberIsUnavailable;
use App\Models\Cabinet;
use App\Models\Member;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;

it('rejects an owner that does not own the selected assignment', function (): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $cabinet = Cabinet::factory()->recycle($team)->createOne();
    $member = Member::factory()->recycle($team)->createOne();

    expect(fn () => resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'cabinet_id' => $cabinet->id,
        'member_id' => $member->id,
    ])))->toThrow(
        MemberDoesNotOwnCabinet::class,
        'The selected member does not own the selected cabinet.',
    );

    assertDatabaseCount(WorkOrder::class, 0);
});

it('rejects an unavailable owner', function (string $state): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $member = match ($state) {
        'another team' => Member::factory()->createOne(),
        'soft deleted' => Member::factory()->trashed()->recycle($team)->createOne(),
    };

    expect(fn () => resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'member_id' => $member->id,
    ])))->toThrow(
        MemberIsUnavailable::class,
        'The selected member is unavailable.',
    );

    assertDatabaseCount(WorkOrder::class, 0);
})->with([
    'another team',
    'soft deleted',
]);
```
