# Job Tests: Ordered Contract Checklist

Keep queue interface checks in unit tests and persisted selection fixtures in integration tests. Invoke the real handler and mock its stated collaborator; these examples establish interface membership and delegation, without running a queue worker or downstream writes.

1. [implements ShouldQueueAfterCommit](01-queue-contract.md)
2. [does not process approved records when the feature is disabled](02-selection-and-delegation.md)
3. [processes only eligible records for the current tenant](02-selection-and-delegation.md)

Keep standalone `it()` declarations and this order within each suite. Separate setup, operation and assertions with blank lines; mock expectations precede the operation. Related fixture declarations may stay consecutive. Preserve excluded fixtures and exact call counts.

Test dispatch in the caller; see [dispatch after creation](../actions/11-create-dispatch.md).
