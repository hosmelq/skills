# Controllers: Write a Nested Record and Return to Its List

Pass the bound parent on creation and the bound record on update. These operations return to the parent list and do not need the created record for the redirect. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\MemberAddresses\CreateMemberAddress;
use App\Actions\MemberAddresses\Inputs\CreateMemberAddressInput;
use App\Actions\MemberAddresses\Inputs\UpdateMemberAddressInput;
use App\Actions\MemberAddresses\UpdateMemberAddress;
use App\Http\Requests\StoreMemberAddressRequest;
use App\Http\Requests\UpdateMemberAddressRequest;
use App\Models\Member;
use App\Models\MemberAddress;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;

class MemberAddressController
{
    public function store(
        StoreMemberAddressRequest $request,
        Team $team,
        Member $member,
        CreateMemberAddress $createMemberAddress,
    ): RedirectResponse {
        $createMemberAddress->handle(
            $member,
            CreateMemberAddressInput::from($request->validated()),
        );

        return to_route('teams.members.addresses.index', [
            'member' => $member,
            'team' => $team,
        ])->toast(__('member_address.created.title'));
    }

    public function update(
        UpdateMemberAddressRequest $request,
        Team $team,
        Member $member,
        MemberAddress $address,
        UpdateMemberAddress $updateMemberAddress,
    ): RedirectResponse {
        $updateMemberAddress->handle(
            $address,
            UpdateMemberAddressInput::from($request->validated()),
        );

        return to_route('teams.members.addresses.index', [
            'member' => $member,
            'team' => $team,
        ])->toast(__('member_address.updated.title'));
    }
}
```
