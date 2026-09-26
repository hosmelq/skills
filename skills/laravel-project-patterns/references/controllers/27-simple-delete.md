# Controllers: Delegate a Delete and Redirect

Delete the bound record, then redirect to its root or nested index with all required parents. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\Facilities\DeleteFacility;
use App\Models\Facility;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;

class FacilityController
{
    public function destroy(
        Team $team,
        Facility $facility,
        DeleteFacility $deleteFacility,
    ): RedirectResponse {
        $deleteFacility->handle($facility);

        return to_route('teams.facilities.index', [
            'team' => $team,
        ])->toast(__('facility.deleted.title'));
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\MemberAddresses\DeleteMemberAddress;
use App\Models\Member;
use App\Models\MemberAddress;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;

class MemberAddressController
{
    public function destroy(
        Team $team,
        Member $member,
        MemberAddress $address,
        DeleteMemberAddress $deleteMemberAddress,
    ): RedirectResponse {
        $deleteMemberAddress->handle($address);

        return to_route('teams.members.addresses.index', [
            'member' => $member,
            'team' => $team,
        ])->toast(__('member_address.deleted.title'));
    }
}
```
