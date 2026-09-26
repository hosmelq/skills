# Actions: Select an Owner Default

Within a transaction, clear other defaults belonging to the same owner, then select the target. Exclude the target from the bulk clear; do not clear another owner’s rows.

```php
<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\MemberAddress;
use Illuminate\Support\Facades\DB;

class SetDefaultMemberAddress
{
    public function handle(MemberAddress $address): void
    {
        DB::transaction(function () use ($address): void {
            MemberAddress::query()
                ->where('member_id', $address->member_id)
                ->whereKeyNot($address)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            $address->update(['is_default' => true]);
        });
    }
}
```
