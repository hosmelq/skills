# Controllers: Map an Unavailable Parent on Reactivation

Deactivation delegates directly. Reactivation catches the unavailable-parent error and returns it on the parent field; both return back.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\Cabinets\DeactivateCabinet;
use App\Actions\Cabinets\ReactivateCabinet;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\Cabinet;
use App\Models\Member;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;

class CabinetDeactivationController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:deactivate,cabinet', only: ['store']),
            new Middleware('can:reactivate,cabinet', only: ['destroy']),
        ];
    }

    public function destroy(
        Team $team,
        Member $member,
        Cabinet $cabinet,
        ReactivateCabinet $reactivateCabinet,
    ): RedirectResponse {
        try {
            $reactivateCabinet->handle($cabinet);
        } catch (CannotUseDeactivatedServicePlan) {
            throw ValidationException::withMessages([
                'service_plan' => __('service_plan.validation.deactivated'),
            ]);
        }

        return back()->toast(__('cabinet.reactivated.title'));
    }

    public function store(
        Team $team,
        Member $member,
        Cabinet $cabinet,
        DeactivateCabinet $deactivateCabinet,
    ): RedirectResponse {
        $deactivateCabinet->handle($cabinet);

        return back()->toast(__('cabinet.deactivated.title'));
    }
}
```
