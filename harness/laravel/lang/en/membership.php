<?php

declare(strict_types=1);

use App\Enums\MembershipRole;

return [

    'roles' => [
        MembershipRole::Owner->value => 'Owner',
        MembershipRole::Staff->value => 'Staff',
    ],

];
