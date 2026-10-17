<?php

declare(strict_types=1);

use function Pest\Laravel\freezeSecond;

use Tests\TestCase;

pest()
    ->extend(TestCase::class)
    ->beforeEach(function (): void {
        freezeSecond();
    })
    ->in('Harness', 'References');
