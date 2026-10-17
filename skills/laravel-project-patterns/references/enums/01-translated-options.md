# Enums: Translated Options

Give an enum shown in forms or lists a translated label per case and an `options()` list of label and value pairs, with labels in the right translation file and a unit test of its options.

- Use `Comparable`, `InvokableCases`, `Options` and `Values`, and put `#[TranslationKey(...)]` on every case. `Options` and `TranslationKey` are application classes from the options concern reference.
- A standalone enum, such as a unit or a mode, gets its own translation file named after the enum in snake_case, keyed by `Case->value`: `weight_unit.kilograms`.
- An enum that describes a model's attribute nests its labels under a plural group in that model's translation file, where the model's other labels live: `membership.roles.owner`. Dotted values nest one array per segment.
- Add the same keys to every locale. Render a single case's label with `trans()->string($case->translationKey())`.
- Test options in the default locale with exact labels; options keep case order.

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
}
```

```php
<?php

declare(strict_types=1);

use App\Enums\MembershipRole;

return [

    'roles' => [
        MembershipRole::Owner->value => 'Owner',
        MembershipRole::Staff->value => 'Staff',
    ],

];
```

```php
<?php

declare(strict_types=1);

use App\Enums\MembershipRole;

it('returns options', function (): void {
    expect(MembershipRole::options())->toBe([
        ['label' => 'Owner', 'value' => 'owner'],
        ['label' => 'Staff', 'value' => 'staff'],
    ]);
});
```

A standalone enum keeps the same shape with its own file:

```php
<?php

declare(strict_types=1);

use App\Enums\WeightUnit;

return [

    WeightUnit::Kilograms->value => 'Kilograms',
    WeightUnit::Pounds->value => 'Pounds',

];
```
