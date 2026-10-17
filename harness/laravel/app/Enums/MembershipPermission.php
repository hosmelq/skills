<?php

declare(strict_types=1);

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string BoardingManage()
 * @method static string BoardingView()
 * @method static string VisitsManage()
 * @method static string VisitsView()
 */
enum MembershipPermission: string
{
    use InvokableCases;
    use Values;

    case BoardingManage = 'boarding.manage';
    case BoardingView = 'boarding.view';
    case VisitsManage = 'visits.manage';
    case VisitsView = 'visits.view';
}
