# Store Tests: Mapping Seeded Province

Pest POST store: Province obtained from seeded lookup maps to parent action; unused action return and collection-index redirect.

This example needs the consuming suite's seeded country/province lookup and compatible factory defaults.

## Stores the record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\MemberAddresses\CreateMemberAddress;
use App\Actions\MemberAddresses\Inputs\CreateMemberAddressInput;
use App\Enums\CountryCode;
use App\Models\Member;
use App\Models\World\State;

describe('store', function (): void {
    it('stores the record', function (): void {
        $member = Member::factory()->createOne();

        $state = State::query()
            ->where('country_code', CountryCode::Nicaragua)
            ->orderBy('name')
            ->firstOrFail();

        login(team: $member->team);

        mock(CreateMemberAddress::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Member $memberArgument, CreateMemberAddressInput $input): bool => $memberArgument->is($member)
                && $input->provinceCode === $state->iso2);

        $response = post(route('teams.members.addresses.store', [
            'team' => $member->team,
            'member' => $member,
        ]), [
            'country_code' => CountryCode::Nicaragua->value,
            'province_code' => $state->iso2,
        ]);

        $response->assertRedirectToRoute('teams.members.addresses.index', [
            'team' => $member->team,
            'member' => $member,
        ])
            ->assertToast('Address created');
    });
});
```
