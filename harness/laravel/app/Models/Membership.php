<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Override;

final class Membership extends Pivot
{
    #[Override]
    public $incrementing = true;
}
