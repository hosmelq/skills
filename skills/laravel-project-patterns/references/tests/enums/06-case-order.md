# Enum Tests: Explicit Case Order

Unit test for custom `orderedCases()` returning enum instances in a deliberate sequence. Assert the literal sequence separately from declaration-order `values()` and translated `options()`.

```php
<?php

declare(strict_types=1);

use App\Enums\WorkflowState;

it('defines ordered cases', function (): void {
    expect(WorkflowState::orderedCases())->toEqual([
        WorkflowState::Open,
        WorkflowState::Queued,
        WorkflowState::InProgress,
        WorkflowState::Ready,
        WorkflowState::Completed,
        WorkflowState::Archived,
        WorkflowState::Blocked,
        WorkflowState::Cancelled,
    ]);
});
```
