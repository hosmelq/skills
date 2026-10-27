<?php

declare(strict_types=1);

namespace App\Enums;

use ArchTech\Enums\Values;

enum CountryCode: string
{
    use Values;

    case Nicaragua = 'NI';
    case UnitedStates = 'US';
}
