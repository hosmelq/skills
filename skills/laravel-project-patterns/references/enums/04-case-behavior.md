# Enums: Case Behavior

Put rules that depend only on the case inside the enum, as instance methods that map every case to a value with an exhaustive `match`, and test them with one dataset row per case.

- Match every case without `default`.
- When the result is another enum's case, return the case rather than its value, and type lists `list<Enum>`.
- Logic that needs data beyond the case belongs in an action.

```php
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
```

```php
<?php

declare(strict_types=1);

use App\Enums\MembershipPermission;
use App\Enums\MembershipRole;

it('returns the default permissions of each role', function (
    MembershipRole $role,
    array $permissions,
): void {
    expect($role->defaultPermissions())->toBe($permissions);
})->with([
    'owner' => [MembershipRole::Owner, MembershipPermission::cases()],
    'staff' => [MembershipRole::Staff, [MembershipPermission::VisitsView]],
]);
```
