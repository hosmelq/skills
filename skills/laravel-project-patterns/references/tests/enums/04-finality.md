# Enum Tests: Final State Predicate

Unit test for a status enum's `isFinal()` predicate using a typed case/boolean dataset. Include every case, both final and nonfinal; use the inspected classification, not assumptions from case names.

```php
<?php

declare(strict_types=1);

use App\Enums\WorkflowState;

it('determines whether a status is final', function (WorkflowState $state, bool $isFinal): void {
    expect($state->isFinal())->toBe($isFinal);
})->with([
    'archived' => [WorkflowState::Archived, true],
    'blocked' => [WorkflowState::Blocked, false],
    'cancelled' => [WorkflowState::Cancelled, true],
    'completed' => [WorkflowState::Completed, true],
    'in progress' => [WorkflowState::InProgress, false],
    'open' => [WorkflowState::Open, false],
    'queued' => [WorkflowState::Queued, false],
    'ready' => [WorkflowState::Ready, false],
]);
```
