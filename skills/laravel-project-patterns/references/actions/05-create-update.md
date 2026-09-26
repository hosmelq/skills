# Actions: Create and Update Through Data

Simple relation-scoped creation and partial model updates through transformed Data. The relation supplies its owner key; tap returns the updated instance without querying it again.

Use the actual input classes with the field typing shown in the input examples. Authorization and validation belong to inspected callers; these bodies do not add them.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Actions\Members\Inputs\CreateMemberInput;
use App\Models\Member;
use App\Models\Team;

class CreateMember
{
    public function handle(Team $team, CreateMemberInput $input): Member
    {
        return $team->members()->create($input->transform());
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Actions\Members\Inputs\UpdateMemberInput;
use App\Models\Member;

class UpdateMember
{
    public function handle(Member $member, UpdateMemberInput $input): Member
    {
        return tap($member)->update($input->transform());
    }
}
```
