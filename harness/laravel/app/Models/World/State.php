<?php

declare(strict_types=1);

namespace App\Models\World;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read int $country_id
 * @property-read string $country_code
 * @property-read null|string $iso2
 * @property-read string $name
 */
#[Connection('world')]
final class State extends Model {}
