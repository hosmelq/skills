# Controllers: Create and Update Owner Settings

Inject the current user for creation, redirect through the returned tenant, and return back after an update. Preserve the typed Request and input contracts. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\Teams\CreateTeam;
use App\Actions\Teams\Inputs\CreateTeamInput;
use App\Actions\Teams\Inputs\UpdateTeamInput;
use App\Actions\Teams\UpdateTeam;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

class TeamController
{
    public function store(
        StoreTeamRequest $request,
        #[CurrentUser] User $user,
        CreateTeam $createTeam,
    ): RedirectResponse {
        $team = $createTeam->handle(
            $user,
            CreateTeamInput::from($request->validated()),
        );

        return to_route('teams.settings.general', ['team' => $team])
            ->toast(__('team.created.title'));
    }

    public function update(
        UpdateTeamRequest $request,
        Team $team,
        UpdateTeam $updateTeam,
    ): RedirectResponse {
        $updateTeam->handle(
            $team,
            UpdateTeamInput::from($request->validated()),
        );

        return back()->toast(__('team.updated.title'));
    }
}
```
