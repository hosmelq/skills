# Models: Deactivation Concern

Implement conditional deactivate/reactivate transitions, state predicates and qualified active/deactivated Eloquent scopes. This concern checks the timestamp; soft-delete-aware eligibility is a separate contract.

Cast `deactivated_at` as `datetime` and allow it in the host model's mass-assignment policy. Its immutable annotation assumes the application's immutable date factory.

```php
<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property-read null|CarbonImmutable $deactivated_at
 */
trait HasDeactivation
{
    public function deactivate(): void
    {
        if ($this->isActive()) {
            $this->update(['deactivated_at' => now()]);
        }
    }

    public function isActive(): bool
    {
        return $this->deactivated_at === null;
    }

    public function isDeactivated(): bool
    {
        return $this->deactivated_at !== null;
    }

    public function reactivate(): void
    {
        if ($this->isDeactivated()) {
            $this->update(['deactivated_at' => null]);
        }
    }

    /**
     * @param Builder<static> $builder
     */
    #[Scope]
    protected function active(Builder $builder): void
    {
        $builder->whereNull($builder->qualifyColumn('deactivated_at'));
    }

    /**
     * @param Builder<static> $builder
     */
    #[Scope]
    protected function deactivated(Builder $builder): void
    {
        $builder->whereNotNull($builder->qualifyColumn('deactivated_at'));
    }
}
```
