# Forms: Preserve Literal Enum Values

Keep frontend literal spelling in string-backed enums: empty strings, hyphens and differently ordered space-separated values remain distinct. Use the exact union declared for the target component; equivalent serialization does not make component-specific enum types interchangeable.

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI\Enums;

enum DescriptionAriaRelevant: string
{
    case Additions = 'additions';
    case AdditionsRemovals = 'additions removals';
    case AdditionsText = 'additions text';
    case All = 'all';
    case Removals = 'removals';
    case RemovalsAdditions = 'removals additions';
    case RemovalsText = 'removals text';
    case Text = 'text';
    case TextAdditions = 'text additions';
    case TextRemovals = 'text removals';
}
```

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI\Enums;

enum DescriptionContentEditable: string
{
    case False = 'false';
    case Inherit = 'inherit';
    case PlaintextOnly = 'plaintext-only';
    case True = 'true';
}
```

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI\Enums;

enum DescriptionPopover: string
{
    case Auto = 'auto';
    case Empty = '';
    case Hint = 'hint';
    case Manual = 'manual';
}
```
