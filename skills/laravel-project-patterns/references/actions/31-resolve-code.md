# Actions: Resolve a Normalized Active Code

Nullable lookup scoped to tenant, normalized code and active state. No match returns null; default soft-delete scope remains in effect.

```php
<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Cabinet;
use App\Models\Team;

class ResolveActiveCabinet
{
    public function handle(Team $team, string $code): null|Cabinet
    {
        return Cabinet::query()
            ->where('team_id', $team->id)
            ->where('normalized_code', Cabinet::normalizeCode($code))
            ->whereNull('deactivated_at')
            ->first();
    }
}
```
