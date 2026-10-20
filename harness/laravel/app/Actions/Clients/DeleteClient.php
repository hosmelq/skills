<?php

declare(strict_types=1);

namespace App\Actions\Clients;

use function HosmelQ\AuditLog\audit_log;

use App\Enums\AuditLogEvent;
use App\Exceptions\CannotDeleteClientWithPets;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

final readonly class DeleteClient
{
    public function handle(Client $client): void
    {
        DB::transaction(function () use ($client): void {
            throw_if($client->pets()->withTrashed()->exists(), CannotDeleteClientWithPets::class);

            $client->addresses()->delete();
            $client->delete();

            audit_log(AuditLogEvent::ClientDeleted)
                ->target($client)
                ->tenant($client->clinic_id)
                ->record();
        });
    }
}
