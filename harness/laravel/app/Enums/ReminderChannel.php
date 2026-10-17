<?php

declare(strict_types=1);

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string Email()
 * @method static string Sms()
 */
enum ReminderChannel: string
{
    use InvokableCases;
    use Values;

    case Email = 'email';
    case Sms = 'sms';
}
