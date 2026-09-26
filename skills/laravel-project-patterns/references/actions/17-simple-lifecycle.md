# Actions: Simple Record Lifecycle

Plain delete, deactivate and reactivate actions delegate to the model and return void. Deletion follows that model’s SoftDeletes contract; the action does not choose another default.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Facilities;

use App\Models\Facility;

class DeleteFacility
{
    public function handle(Facility $facility): void
    {
        $facility->delete();
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\Facilities;

use App\Models\Facility;

class DeactivateFacility
{
    public function handle(Facility $facility): void
    {
        $facility->deactivate();
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\Facilities;

use App\Models\Facility;

class ReactivateFacility
{
    public function handle(Facility $facility): void
    {
        $facility->reactivate();
    }
}
```
