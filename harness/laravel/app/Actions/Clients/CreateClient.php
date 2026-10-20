<?php

declare(strict_types=1);

namespace App\Actions\Clients;

use function HosmelQ\AuditLog\audit_log;

use App\Actions\Clients\Inputs\CreateClientInput;
use App\Enums\AuditLogEvent;
use App\Models\Client;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;

final readonly class CreateClient
{
    public function handle(Clinic $clinic, CreateClientInput $input): Client
    {
        return DB::transaction(function () use ($clinic, $input): Client {
            $client = $clinic->clients()->create($input->transform());

            audit_log(AuditLogEvent::ClientCreated)
                ->target($client)
                ->tenant($client->clinic_id)
                ->record();

            return $client;
        });
    }
}
