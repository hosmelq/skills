# Controllers: Paginate Tenant and Parent Lists

Start from the tenant or parent relation, apply the explicit tenant predicate where required, order latest IDs and convert the paginator to resources. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class MemberController
{
    public function index(Team $team): Response
    {
        $members = $team->members()->latest('id')->paginate();

        return Inertia::render('members/Index', [
            'members' => $members->toResourceCollection(),
            'team' => $team->toResource(),
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class MemberAddressController
{
    public function index(Team $team, Member $member): Response
    {
        $addresses = $member->addresses()->latest('id')->paginate();

        return Inertia::render('members/addresses/Index', [
            'addresses' => $addresses->toResourceCollection(),
            'member' => $member->toResource(),
            'team' => $team->toResource(),
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class CabinetController
{
    public function index(Team $team, Member $member): Response
    {
        $cabinets = $member->cabinets()
            ->where('team_id', $team->id)
            ->latest('id')
            ->paginate();

        return Inertia::render('members/cabinets/Index', [
            'cabinets' => $cabinets->toResourceCollection(),
            'member' => $member->toResource(),
            'team' => $team->toResource(),
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class PlanRateController
{
    public function index(Team $team, ServicePlan $servicePlan, PlanRule $planRule): Response
    {
        $rates = $planRule->rates()->latest('id')->paginate();

        return Inertia::render('service-plans/rates/Index', [
            'planRule' => $planRule->toResource(),
            'rates' => $rates->toResourceCollection(),
            'servicePlan' => $servicePlan->toResource(),
            'team' => $team->toResource(),
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CountryCode;
use App\Enums\FacilityType;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FacilityController
{
    public function index(Request $request, Team $team): Response
    {
        $facilities = $team->facilities()->latest('id')->paginate();

        return Inertia::render('facilities/Index', [
            'countryCodes' => CountryCode::options(),
            'facilities' => $facilities->toResourceCollection(),
            'facilityTypes' => FacilityType::options(),
            'team' => $team->toResource(),
        ]);
    }
}
```
