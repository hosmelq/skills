# Job Tests: After-Commit Queue Contract

A unit test constructs a job with an in-memory model and checks `ShouldQueueAfterCommit`. This verifies the declared interface, without dispatching the job or testing transaction timing.

```php
<?php

declare(strict_types=1);

use App\Jobs\ProvisionTeamCabinets;
use App\Models\Team;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

it('implements ShouldQueueAfterCommit', function (): void {
    $team = new Team();
    $job = new ProvisionTeamCabinets($team);

    expect($job)->toBeInstanceOf(ShouldQueueAfterCommit::class);
});
```
