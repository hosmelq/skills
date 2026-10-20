<?php

declare(strict_types=1);

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string Alphanumeric()
 * @method static string Letters()
 * @method static string Numbers()
 */
enum NanoIDAlphabet: string
{
    use InvokableCases;
    use Values;

    case Alphanumeric = 'abcdefghijklmnopqrstuvwxyz0123456789';
    case Letters = 'abcdefghijklmnopqrstuvwxyz';
    case Numbers = '0123456789';
}
