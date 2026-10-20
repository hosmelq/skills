<?php

declare(strict_types=1);

namespace App\Enums;

use ArchTech\Enums\Values;

enum AuditLogEvent: string
{
    use Values;

    case ClientCreated = 'client.created';
    case ClientDeleted = 'client.deleted';
    case ClientUpdated = 'client.updated';
    case ClinicCreated = 'clinic.created';
    case RoomDeactivated = 'room.deactivated';
    case VisitStatusInitialSet = 'visit_status.initial_set';
    case VisitStatusMoved = 'visit_status.moved';
}
