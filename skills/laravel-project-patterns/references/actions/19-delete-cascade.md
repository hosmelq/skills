# Actions: Delete Related Records Transactionally

Reject historical use, then delete child assignments, addresses and the owner in order within one transaction. Relation bulk deletes differ from loading children and invoking each model’s delete.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Exceptions\CannotDeleteMemberWithWorkOrderHistory;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class DeleteMember
{
    public function handle(Member $member): void
    {
        DB::transaction(function () use ($member): void {
            throw_if(
                $member->workOrders()->withTrashed()->exists(),
                CannotDeleteMemberWithWorkOrderHistory::class
            );

            $member->cabinets()->delete();
            $member->addresses()->delete();
            $member->delete();
        });
    }
}
```
