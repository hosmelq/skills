# Models: Membership and Current Team Selection

Implement nullable membership/ownership checks and current-team selection on a user. Selection authorizes membership or ownership, persists the foreign key and refreshes the cached relation.

`currentTeam()` may persist the first related team when no current ID exists. Preserve that side effect and `setRelation()`. This example relies on the inspected mass-assignment policy allowing `current_team_id` updates.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property-read int $id
 * @property-read null|int $current_team_id
 * @property-read null|Team $currentTeam
 * @property-read Collection<int, Team> $ownedTeams
 * @property-read Collection<int, Team> $teams
 */
class User extends Authenticatable
{
    public function belongsToTeam(null|Team $team): bool
    {
        if (! $team instanceof Team) {
            return false;
        }

        if ($this->ownsTeam($team)) {
            return true;
        }

        return $this->teams->contains($team);
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function currentTeam(): BelongsTo
    {
        if ($this->current_team_id === null && ($team = $this->teams->first()) !== null) {
            $this->switchTeam($team);
        }

        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function isCurrentTeam(Team $team): bool
    {
        if ($this->currentTeam === null) {
            return false;
        }

        return $this->currentTeam->id === $team->id;
    }

    /**
     * @return HasMany<Team, $this>
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function ownsTeam(null|Team $team): bool
    {
        if (! $team instanceof Team) {
            return false;
        }

        return $this->id === $team->owner_id;
    }

    public function switchTeam(Team $team): bool
    {
        if (! $this->belongsToTeam($team)) {
            return false;
        }

        $this->update(['current_team_id' => $team->id]);

        $this->setRelation('currentTeam', $team);

        return true;
    }

    /**
     * @return BelongsToMany<Team, $this, Membership, 'membership'>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
            ->as('membership')
            ->using(Membership::class)
            ->withTimestamps();
    }
}
```
