<?php

declare(strict_types=1);

namespace App\Actions\Clinics;

use function HosmelQ\AuditLog\audit_log;

use App\Actions\Clinics\Inputs\CreateClinicInput;
use App\Actions\VisitStatuses\EnsureInitialVisitStatus;
use App\Enums\AuditLogEvent;
use App\Enums\MembershipRole;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class CreateClinic
{
    public function __construct(
        private EnsureClinicRoles $ensureClinicRoles,
        private EnsureInitialVisitStatus $ensureInitialVisitStatus,
    ) {}

    public function handle(User $user, CreateClinicInput $input): Clinic
    {
        return DB::transaction(function () use ($input, $user): Clinic {
            $clinic = $user->ownedClinics()->create($input->transform());

            $this->ensureClinicRoles->handle($clinic);
            $this->ensureInitialVisitStatus->handle($clinic);

            $clinic->users()->attach($user);

            $user->switchClinic($clinic);
            $user->assignRole(MembershipRole::Owner);

            audit_log(AuditLogEvent::ClinicCreated)
                ->target($clinic)
                ->tenant($clinic->id)
                ->record();

            return $clinic;
        });
    }
}
