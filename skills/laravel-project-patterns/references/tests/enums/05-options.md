# Enum Tests: Translated Options

Unit test for a custom enum `options()` returning an ordered list of translated `label`/backing-`value` pairs. This shape differs from `ArchTech\Enums\Options`, which returns a name-to-value map for backed enums.

Use the Laravel test bootstrap and set the locale whose translations supply the expected labels. Assert literal labels and the full order used by the inspected method (`cases()` or its custom sequence).

```php
<?php

declare(strict_types=1);

use App\Enums\WorkflowState;

it('returns options', function (): void {
    app()->setLocale('en');

    expect(WorkflowState::options())->toBe([
        ['label' => 'Open', 'value' => 'open'],
        ['label' => 'Queued', 'value' => 'queued'],
        ['label' => 'In progress', 'value' => 'in_progress'],
        ['label' => 'Ready', 'value' => 'ready'],
        ['label' => 'Completed', 'value' => 'completed'],
        ['label' => 'Archived', 'value' => 'archived'],
        ['label' => 'Blocked', 'value' => 'blocked'],
        ['label' => 'Cancelled', 'value' => 'cancelled'],
    ]);
});
```
