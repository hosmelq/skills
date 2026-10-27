<?php

declare(strict_types=1);

namespace App\Actions\Users;

use function HosmelQ\AuditLog\audit_log;

use App\Actions\Users\Inputs\CreateUserInput;
use App\Enums\AuditLogEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class CreateUser
{
    public function handle(CreateUserInput $input): User
    {
        return DB::transaction(function () use ($input): User {
            $user = User::query()->create($input->transform());

            audit_log(AuditLogEvent::UserCreated)
                ->actor($user)
                ->target($user)
                ->record();

            return $user;
        });
    }
}
