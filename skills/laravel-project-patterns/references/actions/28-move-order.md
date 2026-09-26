# Actions: Move After a Record or to the Start

A nullable after-target selects between moveAfter and moveToStart on a sortable model. The model supplies group scope; callers validate that the target belongs to the permitted group.

```php
<?php

declare(strict_types=1);

namespace App\Actions\ItemGroups;

use App\Models\ItemGroup;

class MoveItemGroup
{
    public function handle(ItemGroup $itemGroup, null|ItemGroup $afterItemGroup): void
    {
        if ($afterItemGroup instanceof ItemGroup) {
            $itemGroup->moveAfter($afterItemGroup);

            return;
        }

        $itemGroup->moveToStart();
    }
}
```
