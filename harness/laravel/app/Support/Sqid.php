<?php

declare(strict_types=1);

namespace App\Support;

use Sqids\SqidsInterface;

final readonly class Sqid
{
    public function __construct(private SqidsInterface $sqids) {}

    public function decode(string $sqid): null|int
    {
        $numbers = $this->sqids->decode($sqid);

        if (count($numbers) !== 1 || $this->encode($numbers[0]) !== $sqid) {
            return null;
        }

        return $numbers[0];
    }

    public function encode(int $id): string
    {
        return $this->sqids->encode([$id]);
    }
}
