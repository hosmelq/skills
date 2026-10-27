<?php

declare(strict_types=1);

use function Pest\Laravel\actingAs;
use function Pest\Laravel\freezeSecond;

use App\Models\Clinic;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\Authenticatable;
use Tests\TestCase;

pest()
    ->extend(TestCase::class)
    ->beforeEach(function (): void {
        freezeSecond();
    })
    ->in('Harness', 'References');

function login(null|Authenticatable $user = null, null|Clinic $clinic = null): User
{
    $user ??= User::factory()
        ->when($clinic, function (UserFactory $factory, Clinic $clinic): UserFactory {
            return $factory->withClinic($clinic);
        })
        ->createOne();

    actingAs($user);

    return $user;
}
