<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\Options;
use App\Enums\MetaProperties\TranslationKey;
use ArchTech\Enums\Comparable;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string Owner()
 * @method static string Staff()
 */
enum MembershipRole: string
{
    use Comparable;
    use InvokableCases;
    use Options;
    use Values;

    #[TranslationKey('membership.roles.owner')]
    case Owner = 'owner';

    #[TranslationKey('membership.roles.staff')]
    case Staff = 'staff';

    /**
     * @return list<MembershipPermission>
     */
    public function defaultPermissions(): array
    {
        return match ($this) {
            self::Owner => MembershipPermission::cases(),
            self::Staff => [MembershipPermission::VisitsView],
        };
    }
}
