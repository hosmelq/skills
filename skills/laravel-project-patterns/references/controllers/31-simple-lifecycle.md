# Controllers: Deactivate and Reactivate

Store deactivates; destroy reactivates. Both use their own ability. One controller redirects to its index, another returns back.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\Facilities\DeactivateFacility;
use App\Actions\Facilities\ReactivateFacility;
use App\Models\Facility;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FacilityDeactivationController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:deactivate,facility', only: ['store']),
            new Middleware('can:reactivate,facility', only: ['destroy']),
        ];
    }

    public function destroy(
        Team $team,
        Facility $facility,
        ReactivateFacility $reactivateFacility,
    ): RedirectResponse {
        $reactivateFacility->handle($facility);

        return to_route('teams.facilities.index', [
            'team' => $team,
        ])->toast(__('facility.reactivated.title'));
    }

    public function store(
        Team $team,
        Facility $facility,
        DeactivateFacility $deactivateFacility,
    ): RedirectResponse {
        $deactivateFacility->handle($facility);

        return to_route('teams.facilities.index', [
            'team' => $team,
        ])->toast(__('facility.deactivated.title'));
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\ItemGroups\DeactivateItemGroup;
use App\Actions\ItemGroups\ReactivateItemGroup;
use App\Models\ItemGroup;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ItemGroupDeactivationController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:deactivate,item_group', only: ['store']),
            new Middleware('can:reactivate,item_group', only: ['destroy']),
        ];
    }

    public function destroy(
        Team $team,
        ItemGroup $itemGroup,
        ReactivateItemGroup $reactivateItemGroup,
    ): RedirectResponse {
        $reactivateItemGroup->handle($itemGroup);

        return back()->toast(__('item_group.reactivated.title'));
    }

    public function store(
        Team $team,
        ItemGroup $itemGroup,
        DeactivateItemGroup $deactivateItemGroup,
    ): RedirectResponse {
        $deactivateItemGroup->handle($itemGroup);

        return back()->toast(__('item_group.deactivated.title'));
    }
}
```
