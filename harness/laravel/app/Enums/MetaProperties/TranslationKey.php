<?php

declare(strict_types=1);

namespace App\Enums\MetaProperties;

use ArchTech\Enums\Meta\MetaProperty;
use Attribute;
use Override;

#[Attribute]
final class TranslationKey extends MetaProperty
{
    #[Override]
    public static function method(): string
    {
        return 'translationKey';
    }
}
