# Action Tests: Delete with Related Records

Integration tests for owner deletion: reject live or soft-deleted history, soft delete the eligible owner and its related records, and preserve another owner's records.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;

use App\Actions\Members\DeleteMember;
use App\Exceptions\CannotDeleteMemberWithWorkOrderHistory;
use App\Models\Cabinet;
use App\Models\Member;
use App\Models\MemberAddress;
use App\Models\WorkOrder;

it('rejects deleting an owner referenced by an active related record', function (): void {
    $member = Member::factory()->createOne();
    WorkOrder::factory()
        ->for($member)
        ->recycle($member->team)
        ->createOne();

    expect(fn () => resolve(DeleteMember::class)->handle($member))
        ->toThrow(CannotDeleteMemberWithWorkOrderHistory::class, 'Cannot delete a member with work orders.');

    assertNotSoftDeleted($member);
});

it('rejects deleting an owner referenced by a soft deleted related record', function (): void {
    $member = Member::factory()->createOne();
    WorkOrder::factory()
        ->trashed()
        ->for($member)
        ->recycle($member->team)
        ->createOne();

    expect(fn () => resolve(DeleteMember::class)->handle($member))
        ->toThrow(CannotDeleteMemberWithWorkOrderHistory::class, 'Cannot delete a member with work orders.');

    assertNotSoftDeleted($member);
});

it('deletes an owner and its operational records', function (): void {
    $member = Member::factory()->createOne();
    $defaultAddress = MemberAddress::factory()->recycle($member)->default()->createOne();
    $nonDefaultAddress = MemberAddress::factory()->recycle($member)->createOne(['is_default' => false]);
    $cabinet = Cabinet::factory()
        ->recycle($member)
        ->createOne();

    resolve(DeleteMember::class)->handle($member);

    assertSoftDeleted($member);
    assertSoftDeleted($defaultAddress);
    assertSoftDeleted($cabinet);
    assertSoftDeleted($nonDefaultAddress);
});

it('preserves operational records for other owners', function (): void {
    $member = Member::factory()->createOne();
    $otherMember = Member::factory()
        ->recycle($member->team)
        ->createOne();
    $otherAddress = MemberAddress::factory()->recycle($otherMember)->default()->createOne();
    $otherCabinet = Cabinet::factory()
        ->recycle($otherMember)
        ->createOne();

    resolve(DeleteMember::class)->handle($member);

    assertNotSoftDeleted($otherAddress);
    assertNotSoftDeleted($otherCabinet);
});
```
