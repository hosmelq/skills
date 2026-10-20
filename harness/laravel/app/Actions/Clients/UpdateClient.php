<?php

declare(strict_types=1);

namespace App\Actions\Clients;

use function HosmelQ\AuditLog\audit_log;

use App\Actions\Clients\Inputs\UpdateClientInput;
use App\Enums\AuditLogEvent;
use App\Models\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class UpdateClient
{
    public function handle(Client $client, UpdateClientInput $input): Client
    {
        return DB::transaction(function () use ($client, $input): Client {
            $client->update($input->transform());

            /** @var array<string, null|bool|float|int|string> $before */
            $before = Arr::except($client->getPrevious(), 'updated_at');
            /** @var array<string, null|bool|float|int|string> $after */
            $after = Arr::except($client->getChanges(), 'updated_at');

            audit_log(AuditLogEvent::ClientUpdated)
                ->changes(before: $before, after: $after)
                ->target($client)
                ->tenant($client->clinic_id)
                ->record();

            return $client;
        });
    }
}
