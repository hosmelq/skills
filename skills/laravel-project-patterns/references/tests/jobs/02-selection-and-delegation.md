# Job Tests: Selection and Delegation

Integration tests invoke `handle()` directly: a disabled feature makes no collaborator calls; an enabled feature delegates once per approved record in the current tenant, excluding pending, rejected and foreign-tenant records. The collaborator is mocked, so its writes are not tested.

The enrollment factory derives its tenant from its member; recycling the team reaches that default member factory.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;

use App\Actions\Cabinets\ProvisionMemberCabinets;
use App\Enums\EnrollmentStatus;
use App\Jobs\ProvisionTeamCabinets;
use App\Models\Enrollment;
use App\Models\Team;

it('does not process approved records when the feature is disabled', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => false]);

    Enrollment::factory()->approved()->recycle($team)->createOne();

    $provisionMemberCabinets = mock(ProvisionMemberCabinets::class)
        ->shouldNotReceive('handle')
        ->getMock();

    new ProvisionTeamCabinets($team)->handle($provisionMemberCabinets);
});

it('processes only eligible records for the current tenant', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => true]);
    $firstApprovedEnrollment = Enrollment::factory()->approved()->recycle($team)->createOne();
    $secondApprovedEnrollment = Enrollment::factory()->approved()->recycle($team)->createOne();

    Enrollment::factory()->recycle($team)->createOne(['status' => EnrollmentStatus::Pending]);
    Enrollment::factory()->rejected()->recycle($team)->createOne();
    Enrollment::factory()->approved()->createOne();

    $provisionMemberCabinets = mock(ProvisionMemberCabinets::class);

    $provisionMemberCabinets
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (Enrollment $enrollment): bool => $enrollment->is($firstApprovedEnrollment));

    $provisionMemberCabinets
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (Enrollment $enrollment): bool => $enrollment->is($secondApprovedEnrollment));

    new ProvisionTeamCabinets($team)->handle($provisionMemberCabinets);
});
```
