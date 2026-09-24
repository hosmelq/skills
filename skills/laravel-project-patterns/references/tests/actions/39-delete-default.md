# Action Tests: Delete Without Replacing a Default

Integration tests for soft deletion of an address, including a default address: retain another address with a false flag and assert that no new active default is selected.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertSoftDeleted;

use App\Actions\MemberAddresses\DeleteMemberAddress;
use App\Models\MemberAddress;

it('soft deletes a record', function (): void {
    $address = MemberAddress::factory()->createOne();

    resolve(DeleteMemberAddress::class)->handle($address);

    assertSoftDeleted($address);
});

it('leaves the owner without a default address when deleting the default address', function (): void {
    $defaultAddress = MemberAddress::factory()->default()->createOne();
    $otherAddress = MemberAddress::factory()->recycle($defaultAddress->member)->createOne();

    resolve(DeleteMemberAddress::class)->handle($defaultAddress);

    assertSoftDeleted($defaultAddress);

    assertDatabaseHas(MemberAddress::class, [
        'id' => $otherAddress->id,
        'is_default' => false,
    ]);

    assertDatabaseMissing(MemberAddress::class, [
        'member_id' => $defaultAddress->member_id,
        'deleted_at' => null,
        'is_default' => true,
    ]);
});
```
