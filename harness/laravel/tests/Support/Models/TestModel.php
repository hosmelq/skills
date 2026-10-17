<?php

declare(strict_types=1);

namespace Tests\Support\Models;

use App\Models\Concerns\HasDeactivation;
use App\Models\Concerns\HasSqid;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Table('test_models', timestamps: false)]
final class TestModel extends Model implements HasMedia
{
    use HasDeactivation;
    use HasSqid;
    use InteractsWithMedia;
    use SoftDeletes;

    /**
     * @return HasMany<TestModel, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'deactivated_at' => 'datetime',
        ];
    }
}
