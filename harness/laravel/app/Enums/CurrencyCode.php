<?php

declare(strict_types=1);

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string NIO()
 * @method static string USD()
 */
enum CurrencyCode: string
{
    use InvokableCases;
    use Values;

    case NIO = 'NIO';
    case USD = 'USD';
}
