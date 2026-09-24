# Model Tests: Parent-Scoped Uniqueness

Database uniqueness for an enum value under one parent, and for a member/tenant pair. The composite example also proves another member in the same tenant can persist.

Use the canonical title for each equivalent scope.

```php
<?php

declare(strict_types=1);

use App\Enums\CountryCode;
use App\Models\PlanRule;
use Illuminate\Database\UniqueConstraintViolationException;

it('enforces active value uniqueness per parent at the database level', function (): void {
    $planRule = PlanRule::factory()
        ->forCountry(CountryCode::UnitedStates)
        ->createOne();

    expect(fn () => PlanRule::factory()
        ->recycle($planRule->servicePlan)
        ->forCountry(CountryCode::UnitedStates)
        ->createOne())
        ->toThrow(function (UniqueConstraintViolationException $exception): void {
            expect($exception->index)->toBe('plan_rules_active_country_unique');
        });
});
```

Composite membership identity:

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Models\Enrollment;
use App\Models\Member;
use App\Models\Team;
use Illuminate\Database\UniqueConstraintViolationException;

it('enforces one record per member and tenant at the database level', function (): void {
    $team = Team::factory()->createOne();
    $firstMember = Member::factory()->for($team)->createOne();
    $secondMember = Member::factory()->for($team)->createOne();

    Enrollment::factory()->recycle($firstMember)->createOne();

    $secondEnrollment = Enrollment::factory()
        ->recycle($secondMember)
        ->createOne();

    assertDatabaseHas(Enrollment::class, [
        'member_id' => $secondMember->id,
        'id' => $secondEnrollment->id,
        'team_id' => $team->id,
    ]);

    expect(fn () => Enrollment::factory()
        ->recycle($firstMember)
        ->createOne())->toThrow(function (UniqueConstraintViolationException $exception): void {
            expect($exception->index)->toBe(
                'enrollments_member_team_unique',
            );
        });
});
```
