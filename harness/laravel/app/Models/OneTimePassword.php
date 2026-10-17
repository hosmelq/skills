<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\OneTimePasswordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Override;

/**
 * @property-read int $id
 * @property-read string $code
 * @property-read string $email
 * @property-read CarbonImmutable $expires_at
 * @property-read null|CarbonImmutable $used_at
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 */
final class OneTimePassword extends Model
{
    /** @use HasFactory<OneTimePasswordFactory> */
    use HasFactory;

    use Prunable;

    /**
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        $now = now();

        return self::query()->where(static function (Builder $query) use ($now): void {
            $query->where('expires_at', '<=', $now)->whereNull('used_at');
        })->orWhere(static function (Builder $query) use ($now): void {
            $query->whereNotNull('used_at')->where('used_at', '<=', $now->subMonth());
        });
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }
}
