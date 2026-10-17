<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\Options;
use App\Enums\MetaProperties\TranslationKey;
use ArchTech\Enums\Comparable;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string Cancelled()
 * @method static string CheckedIn()
 * @method static string Completed()
 * @method static string InExam()
 */
enum VisitStage: string
{
    use Comparable;
    use InvokableCases;
    use Options;
    use Values;

    #[TranslationKey('visit_status.stages.cancelled')]
    case Cancelled = 'cancelled';

    #[TranslationKey('visit_status.stages.checked_in')]
    case CheckedIn = 'checked_in';

    #[TranslationKey('visit_status.stages.completed')]
    case Completed = 'completed';

    #[TranslationKey('visit_status.stages.in_exam')]
    case InExam = 'in_exam';

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::orderedCases() as $case) {
            $options[] = [
                'label' => trans()->string($case->translationKey()),
                'value' => $case->value,
            ];
        }

        return $options;
    }

    /**
     * @return list<self>
     */
    public static function orderedCases(): array
    {
        return [
            self::CheckedIn,
            self::InExam,
            self::Completed,
            self::Cancelled,
        ];
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::Cancelled, self::Completed => true,
            default => false,
        };
    }
}
