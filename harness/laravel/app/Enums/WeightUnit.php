<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\Options;
use App\Enums\MetaProperties\TranslationKey;
use ArchTech\Enums\Comparable;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string Kilograms()
 * @method static string Pounds()
 */
enum WeightUnit: string
{
    use Comparable;
    use InvokableCases;
    use Options;
    use Values;

    #[TranslationKey('weight_unit.kilograms')]
    case Kilograms = 'kilograms';

    #[TranslationKey('weight_unit.pounds')]
    case Pounds = 'pounds';
}
