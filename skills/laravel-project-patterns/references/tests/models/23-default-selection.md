# Model Tests: Persisted Default Selection

Database default relation and uniqueness: multiple non-default children, independent defaults for separate parents, one active default per parent, and loading exactly the persisted default.

The relation test belongs in the parent model file.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Models\Member;
use App\Models\MemberAddress;
use Illuminate\Database\UniqueConstraintViolationException;

it('allows multiple non-default records per parent', function (): void {
    $member = Member::factory()->createOne();

    MemberAddress::factory()->recycle($member)->createOne(['is_default' => false]);
    MemberAddress::factory()->recycle($member)->createOne(['is_default' => false]);

    $defaultAddressCount = $member->addresses()->where('is_default', true)->count();
    $addressCount = $member->addresses()->count();

    expect($defaultAddressCount)->toBe(0)
        ->and($addressCount)->toBe(2);
});

it('isolates default records between parents', function (): void {
    $firstMember = Member::factory()->createOne();
    $secondMember = Member::factory()->createOne();

    $firstAddress = MemberAddress::factory()->recycle($firstMember)->createOne(['is_default' => true]);
    $secondAddress = MemberAddress::factory()->recycle($secondMember)->createOne(['is_default' => true]);

    assertDatabaseHas(MemberAddress::class, [
        'id' => $firstAddress->id,
        'member_id' => $firstMember->id,
        'is_default' => true,
    ]);
    assertDatabaseHas(MemberAddress::class, [
        'id' => $secondAddress->id,
        'member_id' => $secondMember->id,
        'is_default' => true,
    ]);
});

it('enforces one active default per parent at the database level', function (): void {
    $member = Member::factory()->createOne();

    MemberAddress::factory()->recycle($member)->default()->createOne();

    expect(fn () => MemberAddress::factory()->recycle($member)->default()->createOne())
        ->toThrow(function (UniqueConstraintViolationException $exception): void {
            expect($exception->index)->toBe('member_addresses_active_default_unique');
        });
});
```

Scoped default relation:

```php
<?php

declare(strict_types=1);

use App\Models\Member;
use App\Models\MemberAddress;

it('loads only the persisted default relation', function (): void {
    $member = Member::factory()->createOne();

    $nonDefaultAddress = MemberAddress::factory()->recycle($member)->createOne(['is_default' => false]);
    $defaultAddress = MemberAddress::factory()->recycle($member)->createOne(['is_default' => true]);

    $member->load('defaultAddress');

    expect($member->defaultAddress)
        ->not->toBeNull()
        ->is($defaultAddress)->toBeTrue()
        ->is($nonDefaultAddress)->toBeFalse()
        ->is_default->toBeTrue();
});
```
