# Destroy Tests: History Assignment And Required Initial Errors

DELETE destroy delegates to an action that rejects history, removal of a required active initial record, or assigned records. Mock the action and assert exact exception-to-field/message mapping through redirect-back errors; these fixtures do not create the underlying history or assignments.

Use ordinary authorized fixtures so the request reaches the mocked action. These tests verify controller exception translation, not the action rule itself.

## History

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\Members\DeleteMember;
use App\Exceptions\CannotDeleteMemberWithWorkOrderHistory;
use App\Models\Member;

describe('destroy', function (): void {
    it('maps an existing history rejection to validation', function (): void {
        $member = Member::factory()->createOne();

        login(team: $member->team);

        mock(DeleteMember::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Member $memberArgument): bool => $memberArgument->is($member))
            ->andThrow(new CannotDeleteMemberWithWorkOrderHistory());

        $response = delete(route('teams.members.destroy', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertRedirectBackWithErrors([
            'member' => 'This member has work order history and cannot be deleted.',
        ]);
    });
});
```

## Required Initial Record And Assigned Status

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\WorkOrderStatuses\DeleteWorkOrderStatus;
use App\Exceptions\CannotDeleteInitialWorkOrderStatus;
use App\Exceptions\CannotDeleteWorkOrderStatus;
use App\Models\WorkOrderStatus;

describe('destroy', function (): void {
    it('maps a required active initial record rejection to validation', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->createOne();

        login(team: $workOrderStatus->team);

        mock(DeleteWorkOrderStatus::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (WorkOrderStatus $workOrderStatusArgument): bool => $workOrderStatusArgument->is($workOrderStatus))
            ->andThrow(new CannotDeleteInitialWorkOrderStatus());

        $response = delete(route('teams.work-order-statuses.destroy', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]));

        $response->assertRedirectBackWithErrors([
            'work_order_status' => 'The team must keep an active initial work order status.',
        ]);
    });

    it('maps an assigned record rejection to validation', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->createOne();

        login(team: $workOrderStatus->team);

        mock(DeleteWorkOrderStatus::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (WorkOrderStatus $workOrderStatusArgument): bool => $workOrderStatusArgument->is($workOrderStatus))
            ->andThrow(new CannotDeleteWorkOrderStatus());

        $response = delete(route('teams.work-order-statuses.destroy', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]));

        $response->assertRedirectBackWithErrors([
            'work_order_status' => 'This work order status is assigned to one or more work orders and cannot be deleted.',
        ]);
    });
});
```

## Assigned Group

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\ItemGroups\DeleteItemGroup;
use App\Exceptions\CannotDeleteItemGroup;
use App\Models\ItemGroup;

describe('destroy', function (): void {
    it('maps an assigned record rejection to validation', function (): void {
        $itemGroup = ItemGroup::factory()->createOne();

        login(team: $itemGroup->team);

        mock(DeleteItemGroup::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                ItemGroup $itemGroupArgument
            ): bool => $itemGroupArgument->is($itemGroup))
            ->andThrow(new CannotDeleteItemGroup());

        $response = delete(route('teams.item-groups.destroy', [
            'team' => $itemGroup->team,
            'item_group' => $itemGroup,
        ]));

        $response->assertRedirectBackWithErrors([
            'item_group' => 'This item group is assigned to one or more work order lines and cannot be deleted.',
        ]);
    });
});
```
