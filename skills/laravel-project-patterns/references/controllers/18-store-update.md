# Controllers: Delegate Validated Create and Update Input

Pass only validated fields through the typed input. Use the created record for the show redirect; update uses the bound record. Toast messages use the registered project macro. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\Members\CreateMember;
use App\Actions\Members\Inputs\CreateMemberInput;
use App\Actions\Members\Inputs\UpdateMemberInput;
use App\Actions\Members\UpdateMember;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;

class MemberController
{
    public function store(
        StoreMemberRequest $request,
        Team $team,
        CreateMember $createMember,
    ): RedirectResponse {
        $member = $createMember->handle(
            $team,
            CreateMemberInput::from($request->validated()),
        );

        return to_route('teams.members.show', [
            'member' => $member,
            'team' => $team,
        ])->toast(__('member.created.title'));
    }

    public function update(
        UpdateMemberRequest $request,
        Team $team,
        Member $member,
        UpdateMember $updateMember,
    ): RedirectResponse {
        $updateMember->handle(
            $member,
            UpdateMemberInput::from($request->validated()),
        );

        return to_route('teams.members.show', [
            'member' => $member,
            'team' => $team,
        ])->toast(__('member.updated.title'));
    }
}
```
