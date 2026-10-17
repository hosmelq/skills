<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

use App\Enums\MetaProperties\TranslationKey;
use ArchTech\Enums\Meta\Meta;
use ArchTech\Enums\Metadata;
use BackedEnum;

/**
 * @method string translationKey()
 */
#[Meta(TranslationKey::class)]
trait Options
{
    use Metadata;

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public static function options(): array
    {
        /** @var array<int, array{label: string, value: string}> */
        return collect(static::cases())->map(fn (BackedEnum $case): array => [
            'label' => trans()->string($case->translationKey()),
            'value' => $case->value,
        ])->all();
    }
}
