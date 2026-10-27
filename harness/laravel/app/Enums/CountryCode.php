<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\Options;
use App\Enums\MetaProperties\TranslationKey;
use ArchTech\Enums\Values;

enum CountryCode: string
{
    use Options;
    use Values;

    #[TranslationKey('country.codes.NI')]
    case Nicaragua = 'NI';

    #[TranslationKey('country.codes.US')]
    case UnitedStates = 'US';
}
