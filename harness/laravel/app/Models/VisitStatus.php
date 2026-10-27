<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VisitStage;
use App\Models\Concerns\HasSqid;
use Carbon\CarbonImmutable;
use Database\Factories\VisitStatusFactory;
use HosmelQ\AuditLog\Contracts\HasAuditLogIdentity;
use HosmelQ\AuditLog\Support\AuditLogIdentity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @property-read int $id
 * @property-read int $clinic_id
 * @property-read null|CarbonImmutable $deactivated_at
 * @property-read bool $is_initial
 * @property-read string $name
 * @property-read int $sort_order
 * @property-read VisitStage $stage
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read null|CarbonImmutable $deleted_at
 * @property-read Clinic $clinic
 */
final class VisitStatus extends Model implements HasAuditLogIdentity, Sortable
{
    /** @use HasFactory<VisitStatusFactory> */
    use HasFactory;

    use HasSqid;
    use SoftDeletes;
    use SortableTrait;

    public function auditLogIdentity(): AuditLogIdentity
    {
        return new AuditLogIdentity(
            id: $this->id,
            type: $this->getMorphClass(),
            name: $this->name,
        );
    }

    /**
     * @return Builder<static>
     */
    public function buildSortQuery(): Builder
    {
        return self::query()->where('clinic_id', $this->clinic_id)->where('stage', $this->stage);
    }

    public function canBeInitial(): bool
    {
        return $this->isActive() && $this->stage === VisitStage::CheckedIn;
    }

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function isActive(): bool
    {
        return $this->deactivated_at === null && ! $this->trashed();
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'deactivated_at' => 'datetime',
            'is_initial' => 'boolean',
            'stage' => VisitStage::class,
        ];
    }
}
