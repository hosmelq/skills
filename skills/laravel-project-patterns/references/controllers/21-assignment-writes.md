# Controllers: Map Assignment Creation Errors

Translate duplicate assignment and inactive-parent failures to the selected parent field. Creation redirects through the new record; update delegates the supplied fields. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\Cabinets\CreateCabinet;
use App\Actions\Cabinets\Inputs\CreateCabinetInput;
use App\Actions\Cabinets\Inputs\UpdateCabinetInput;
use App\Actions\Cabinets\UpdateCabinet;
use App\Exceptions\CannotCreateCabinet;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Http\Requests\StoreCabinetRequest;
use App\Http\Requests\UpdateCabinetRequest;
use App\Models\Cabinet;
use App\Models\Member;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class CabinetController
{
    public function store(
        StoreCabinetRequest $request,
        Team $team,
        Member $member,
        CreateCabinet $createCabinet,
    ): RedirectResponse {
        try {
            $cabinet = $createCabinet->handle(
                $member,
                CreateCabinetInput::from($request->validated()),
            );
        } catch (CannotCreateCabinet) {
            throw ValidationException::withMessages([
                'service_plan_id' => __('cabinet.validation.service_plan_already_assigned'),
            ]);
        } catch (CannotUseDeactivatedServicePlan) {
            throw ValidationException::withMessages([
                'service_plan_id' => __('service_plan.validation.deactivated'),
            ]);
        }

        return to_route('teams.members.cabinets.show', [
            'cabinet' => $cabinet,
            'member' => $member,
            'team' => $team,
        ])->toast(__('cabinet.created.title'));
    }

    public function update(
        UpdateCabinetRequest $request,
        Team $team,
        Member $member,
        Cabinet $cabinet,
        UpdateCabinet $updateCabinet,
    ): RedirectResponse {
        $updateCabinet->handle(
            $cabinet,
            UpdateCabinetInput::from($request->validated()),
        );

        return to_route('teams.members.cabinets.show', [
            'cabinet' => $cabinet,
            'member' => $member,
            'team' => $team,
        ])->toast(__('cabinet.updated.title'));
    }
}
```
