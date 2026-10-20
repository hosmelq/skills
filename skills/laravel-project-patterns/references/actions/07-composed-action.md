# Actions: Composed Action

Create a tenant for the signed-in user and run the setup actions every new tenant needs, inside one transaction.

- Inject the setup actions into the constructor and call them in the same transaction.
- Attach the user, switch them into the tenant and assign the owner role after the roles exist.
- Record an `AuditLogEvent` case, added alphabetically, inside the transaction, targeting the record and its tenant.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Clinics\Inputs;

use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
final class CreateClinicInput extends Data
{
    public function __construct(
        public readonly string $consultationFee,
        public readonly CountryCode $countryCode,
        public readonly string $name,
        public readonly bool|Optional $acceptsEmergencies,
        public readonly CurrencyCode|Optional $currencyCode,
        public readonly null|Optional|string $phoneNumber,
        public readonly int|Optional $visitDurationMinutes,
    ) {}
}
```

```php
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

            audit_log(AuditLogEvent::ClinicCreated)->target($clinic)->tenant($clinic->id)->record();

            return $clinic;
        });
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\Clinics\CreateClinic;
use App\Actions\Clinics\Inputs\CreateClinicInput;
use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Enums\MembershipRole;
use App\Models\Clinic;
use App\Models\User;
use App\Models\VisitStatus;

it('creates a clinic for the user and switches to it', function (): void {
    $user = User::factory()->createOne();
    $clinic = resolve(CreateClinic::class)->handle(
        $user,
        CreateClinicInput::from([
            'consultation_fee' => '25.00',
            'country_code' => CountryCode::Nicaragua,
            'name' => 'Downtown Clinic',
        ]),
    );

    assertDatabaseHas(Clinic::class, [
        'id' => $clinic->id,
        'owner_id' => $user->id,
        'accepts_emergencies' => false,
        'consultation_fee' => '25.00',
        'country_code' => CountryCode::Nicaragua,
        'currency_code' => CurrencyCode::USD,
        'name' => 'Downtown Clinic',
        'visit_duration_minutes' => 30,
    ]);
    assertDatabaseHas(VisitStatus::class, [
        'clinic_id' => $clinic->id,
        'is_initial' => true,
    ]);

    expect($user->refresh())
        ->current_clinic_id->toBe($clinic->id)
        ->hasRole(MembershipRole::Owner)->toBeTrue();
});
```
