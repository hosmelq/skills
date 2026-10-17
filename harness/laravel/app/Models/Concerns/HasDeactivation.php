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
