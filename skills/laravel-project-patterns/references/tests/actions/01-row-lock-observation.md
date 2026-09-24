# Action Tests: Observe a Row-Lock Query

A local helper for observing `FOR UPDATE` SQL fragments and a candidate ID in bindings. It does not prove which row is selected, lock duration, rollback or concurrent contention.

Register before the action; the assertion runs at application teardown. This broad match assumes quoted identifiers and lowercase `for update`. Use a driver that emits that clause; SQLite omits it. The ID may belong to another column: verify the key predicate and binding position separately when testing a specific row lock.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;

/**
 * @param iterable<Model>|Model $models
 */
function assertRowLockQueryObserved(iterable|Model $models, null|string $connection = null): void
{
    $models = is_iterable($models) ? $models : [$models];
    $expectedQueries = collect($models)
        ->map(function (Model $model) use ($connection): array {
            return [
                'connection' => $connection ?? $model->getConnectionName(),
                'key' => $model->getKey(),
                'keyName' => $model->getKeyName(),
                'table' => $model->getTable(),
            ];
        });
    $queries = [];

    Event::listen(QueryExecuted::class, function (QueryExecuted $query) use (&$queries, $expectedQueries): void {
        if ($expectedQueries->contains(fn (array $expected): bool => $expected['connection'] === null || $query->connectionName === $expected['connection'])) {
            $queries[] = [
                'bindings' => $query->bindings,
                'connection' => $query->connectionName,
                'sql' => $query->sql,
            ];
        }
    });

    test()->beforeApplicationDestroyed(function () use (&$queries, $expectedQueries): void {
        foreach ($expectedQueries as $expected) {
            test()->assertTrue(
                collect($queries)->contains(fn (array $query): bool => ($expected['connection'] === null || $query['connection'] === $expected['connection'])
                    && str_contains($query['sql'], sprintf('"%s"', $expected['table']))
                    && str_contains($query['sql'], sprintf('"%s"', $expected['keyName']))
                    && in_array($expected['key'], $query['bindings'], true)
                    && str_contains($query['sql'], 'for update')),
                sprintf(
                    'Expected FOR UPDATE query fragments%s for table [%s], key [%s], and candidate binding [%s].',
                    $expected['connection'] === null ? '' : sprintf(' on the [%s] connection', $expected['connection']),
                    $expected['table'],
                    $expected['keyName'],
                    $expected['key'],
                )
            );
        }
    });
}
```
