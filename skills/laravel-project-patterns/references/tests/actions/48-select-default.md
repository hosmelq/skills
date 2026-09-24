# Action Tests: Select an Owner Default

Integration tests for selecting one default per owner: clear the prior active default, retain flags on soft-deleted rows and preserve another owner's selection.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\SetDefaultMemberAddress;
use App\Models\Member;
use App\Models\MemberAddress;

it('sets an owner address as default and clears the current active default', function (): void {
    $member = Member::factory()->createOne();

    $defaultAddress = MemberAddress::factory()->recycle($member)->default()->createOne();
    $newDefaultAddress = MemberAddress::factory()->recycle($member)->createOne();

    resolve(SetDefaultMemberAddress::class)->handle($newDefaultAddress);

    assertDatabaseHas(MemberAddress::class, [
        'id' => $defaultAddress->id,
        'is_default' => false,
    ]);
    assertDatabaseHas(MemberAddress::class, [
        'id' => $newDefaultAddress->id,
        'is_default' => true,
    ]);
});

it('does not clear deleted default addresses', function (): void {
    $member = Member::factory()->createOne();

    $deletedDefaultAddress = MemberAddress::factory()
        ->trashed()
        ->recycle($member)
        ->default()
        ->createOne();

    $newDefaultAddress = MemberAddress::factory()->recycle($member)->createOne();

    resolve(SetDefaultMemberAddress::class)->handle($newDefaultAddress);

    assertDatabaseHas(MemberAddress::class, [
        'id' => $deletedDefaultAddress->id,
        'is_default' => true,
    ]);
    assertDatabaseHas(MemberAddress::class, [
        'id' => $newDefaultAddress->id,
        'is_default' => true,
    ]);
});

it('only clears default addresses for the same owner', function (): void {
    $firstMember = Member::factory()->createOne();
    $secondMember = Member::factory()->createOne();

    $firstMemberDefaultAddress = MemberAddress::factory()->recycle($firstMember)->default()->createOne();
    $secondMemberDefaultAddress = MemberAddress::factory()->recycle($secondMember)->default()->createOne();
    $newDefaultAddress = MemberAddress::factory()->recycle($firstMember)->createOne();

    resolve(SetDefaultMemberAddress::class)->handle($newDefaultAddress);

    assertDatabaseHas(MemberAddress::class, [
        'id' => $firstMemberDefaultAddress->id,
        'is_default' => false,
    ]);
    assertDatabaseHas(MemberAddress::class, [
        'id' => $secondMemberDefaultAddress->id,
        'is_default' => true,
    ]);
    assertDatabaseHas(MemberAddress::class, [
        'id' => $newDefaultAddress->id,
        'is_default' => true,
    ]);
});
```
