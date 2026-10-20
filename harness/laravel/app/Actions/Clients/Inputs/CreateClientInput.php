<?php

declare(strict_types=1);

namespace App\Actions\Clients\Inputs;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
final class CreateClientInput extends Data
{
    public function __construct(
        public readonly null|Optional|string $email,
        public readonly null|Optional|string $firstName,
        public readonly null|Optional|string $lastName,
        public readonly null|Optional|string $phoneNumber,
    ) {}
}
