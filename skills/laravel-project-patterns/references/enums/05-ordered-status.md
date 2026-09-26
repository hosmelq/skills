# Enums: Ordered Options and Final States

Implement a status enum with a custom display sequence: orderedCases() returns cases in display order, options() follows it, and values() keeps declaration order. isFinal() preserves true and false classifications.

Use the local `Options`, `TranslationKey` and string translation helper from the [metadata example](04-translated-options.md). Keep the deliberate display sequence and every final/nonfinal classification; translate labels at call time.

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use function App\__;

use App\Enums\Concerns\Options;
use App\Enums\MetaProperties\TranslationKey;
use ArchTech\Enums\Comparable;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string Archived()
 * @method static string Blocked()
 * @method static string Cancelled()
 * @method static string Completed()
 * @method static string InProgress()
 * @method static string Open()
 * @method static string Queued()
 * @method static string Ready()
 */
enum WorkflowState: string
{
    use Comparable;
    use InvokableCases;
    use Options;
    use Values;

    #[TranslationKey('workflow.states.archived')]
    case Archived = 'archived';

    #[TranslationKey('workflow.states.blocked')]
    case Blocked = 'blocked';

    #[TranslationKey('workflow.states.cancelled')]
    case Cancelled = 'cancelled';

    #[TranslationKey('workflow.states.completed')]
    case Completed = 'completed';

    #[TranslationKey('workflow.states.in_progress')]
    case InProgress = 'in_progress';

    #[TranslationKey('workflow.states.open')]
    case Open = 'open';

    #[TranslationKey('workflow.states.queued')]
    case Queued = 'queued';

    #[TranslationKey('workflow.states.ready')]
    case Ready = 'ready';

    /**
     * @return list<array{label: string, value: string}>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::orderedCases() as $case) {
            $options[] = [
                'label' => __($case->translationKey()),
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
            self::Open,
            self::Queued,
            self::InProgress,
            self::Ready,
            self::Completed,
            self::Archived,
            self::Blocked,
            self::Cancelled,
        ];
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::Archived, self::Cancelled, self::Completed => true,
            default => false,
        };
    }
}
```
