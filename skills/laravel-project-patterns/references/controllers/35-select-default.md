# Controllers: Select a Default or Initial Record

Authorize update on the bound record, call the dedicated action and return its toast. Initial selection translates a state error; default selection returns to the parent list.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\SetDefaultMemberAddress;
use App\Models\Member;
use App\Models\MemberAddress;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SetDefaultMemberAddressController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:update,address'),
        ];
    }

    public function __invoke(
        SetDefaultMemberAddress $setDefaultMemberAddress,
        Team $team,
        Member $member,
        MemberAddress $address
    ): RedirectResponse {
        $setDefaultMemberAddress->handle($address);

        return to_route('teams.members.addresses.index', [
            'member' => $member,
            'team' => $team,
        ])->toast(__('member_address.default_set.title'));
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\WorkOrderStatuses\SetInitialWorkOrderStatus;
use App\Exceptions\CannotSetInitialWorkOrderStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;

class InitialWorkOrderStatusController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:update,work_order_status'),
        ];
    }

    public function store(
        SetInitialWorkOrderStatus $setInitialWorkOrderStatus,
        Team $team,
        WorkOrderStatus $workOrderStatus
    ): RedirectResponse {
        try {
            $setInitialWorkOrderStatus->handle($workOrderStatus);
        } catch (CannotSetInitialWorkOrderStatus) {
            throw ValidationException::withMessages([
                'work_order_status' => __(
                    'work_order_status.validation.initial_must_be_active_open',
                ),
            ]);
        }

        return back()->toast(__('work_order_status.initial.title'));
    }
}
```
