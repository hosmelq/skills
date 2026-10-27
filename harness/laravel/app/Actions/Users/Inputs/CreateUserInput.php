<?php

declare(strict_types=1);

namespace App\Actions\Users\Inputs;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
final class CreateUserInput extends Data
{
    public function __construct(
        public readonly null|Optional|string $appleEmail,
        public readonly null|Optional|string $appleId,
        public readonly string $email,
        public readonly null|CarbonImmutable|Optional $emailVerifiedAt,
        public readonly null|Optional|string $firstName,
        public readonly null|Optional|string $googleEmail,
        public readonly null|Optional|string $googleId,
        public readonly null|Optional|string $lastName,
        public readonly null|Optional|string $password,
    ) {}
}
