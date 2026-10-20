<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Clinics\EnsureClinicRoles;
use App\Actions\VisitStatuses\EnsureInitialVisitStatus;
use App\Enums\MembershipRole;
use App\Models\Client;
use App\Models\Clinic;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

final class LocalSeeder extends Seeder
{
    public function run(
        EnsureClinicRoles $ensureClinicRoles,
        EnsureInitialVisitStatus $ensureInitialVisitStatus,
    ): void {
        $user = User::factory()->createOne(['email' => 'test@example.com']);
        $clinics = Clinic::factory()
            ->count(2)
            ->for($user, 'owner')
            ->sequence(['name' => 'Local'], [])
            ->create();

        $user->clinics()->sync($clinics);

        $clinics->each(function (Clinic $clinic) use (
            $ensureClinicRoles,
            $ensureInitialVisitStatus,
            $user
        ): void {
            $ensureClinicRoles->handle($clinic);
            $ensureInitialVisitStatus->handle($clinic);

            $user->switchClinic($clinic);
            $user->assignRole(MembershipRole::Owner);

            Client::factory()->for($clinic)->withDefaultAddress()->createOne();
            Room::factory()->for($clinic)->count(4)->create();
        });
    }
}
