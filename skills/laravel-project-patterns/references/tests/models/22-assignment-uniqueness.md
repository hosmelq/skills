# Model Tests: Assignment and Normalized Code Uniqueness

Database assignment uniqueness across two parent roles, normalized-code uniqueness per tenant, deactivated assignment reservation and reuse after soft deletion. Other combinations remain valid.

Explicit foreign keys isolate the intended constraint; keep the other unique key different. “Active” here excludes soft deletion, not deactivation.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Models\Cabinet;
use App\Models\Member;
use App\Models\ServicePlan;
use App\Models\Team;
use Illuminate\Database\UniqueConstraintViolationException;

it('enforces one active assignment per parent pair at the database level', function (): void {
    $team = Team::factory()->createOne();
    $firstMember = Member::factory()->recycle($team)->createOne();
    $secondMember = Member::factory()->recycle($team)->createOne();
    $firstServicePlan = ServicePlan::factory()->recycle($team)->createOne();
    $secondServicePlan = ServicePlan::factory()->recycle($team)->createOne();

    Cabinet::factory()->createOne([
        'code' => 'DEMO-100001',
        'member_id' => $firstMember->id,
        'normalized_code' => 'DEMO100001',
        'service_plan_id' => $firstServicePlan->id,
        'team_id' => $team->id,
    ]);
    $sameMemberCabinet = Cabinet::factory()->createOne([
        'member_id' => $firstMember->id,
        'service_plan_id' => $secondServicePlan->id,
        'team_id' => $team->id,
    ]);
    $sameServicePlanCabinet = Cabinet::factory()->createOne([
        'member_id' => $secondMember->id,
        'service_plan_id' => $firstServicePlan->id,
        'team_id' => $team->id,
    ]);

    assertDatabaseHas(Cabinet::class, [
        'id' => $sameMemberCabinet->id,
        'member_id' => $firstMember->id,
        'service_plan_id' => $secondServicePlan->id,
        'team_id' => $team->id,
    ]);
    assertDatabaseHas(Cabinet::class, [
        'id' => $sameServicePlanCabinet->id,
        'member_id' => $secondMember->id,
        'service_plan_id' => $firstServicePlan->id,
        'team_id' => $team->id,
    ]);

    expect(fn () => Cabinet::factory()->createOne([
        'code' => 'DEMO-100002',
        'member_id' => $firstMember->id,
        'normalized_code' => 'DEMO100002',
        'service_plan_id' => $firstServicePlan->id,
        'team_id' => $team->id,
    ]))->toThrow(function (UniqueConstraintViolationException $exception): void {
        expect($exception->index)->toBe('cabinets_active_member_service_plan_unique');
    });
});

it('enforces normalized code uniqueness per tenant at the database level', function (): void {
    $cabinet = Cabinet::factory()->createOne([
        'code' => 'DEMO-100003',
        'normalized_code' => 'DEMO100003',
    ]);
    $member = Member::factory()->recycle($cabinet->team)->createOne();
    $servicePlan = ServicePlan::factory()->recycle($cabinet->team)->createOne();

    expect(fn () => Cabinet::factory()->createOne([
        'code' => 'DEMO 100003',
        'member_id' => $member->id,
        'normalized_code' => 'DEMO100003',
        'service_plan_id' => $servicePlan->id,
        'team_id' => $cabinet->team_id,
    ]))->toThrow(function (UniqueConstraintViolationException $exception): void {
        expect($exception->index)->toBe('cabinets_active_normalized_code_unique');
    });
});

it('keeps deactivated assignments reserved at the database level', function (): void {
    $cabinet = Cabinet::factory()->deactivated()->createOne();

    expect(fn () => Cabinet::factory()->createOne([
        'member_id' => $cabinet->member_id,
        'service_plan_id' => $cabinet->service_plan_id,
        'team_id' => $cabinet->team_id,
    ]))->toThrow(function (UniqueConstraintViolationException $exception): void {
        expect($exception->index)->toBe('cabinets_active_member_service_plan_unique');
    });
});

it('allows reusing assignments after soft deletion', function (): void {
    $cabinet = Cabinet::factory()->trashed()->createOne();

    $replacement = Cabinet::factory()->createOne([
        'member_id' => $cabinet->member_id,
        'service_plan_id' => $cabinet->service_plan_id,
        'team_id' => $cabinet->team_id,
    ]);

    assertDatabaseHas(Cabinet::class, [
        'deleted_at' => null,
        'id' => $replacement->id,
        'member_id' => $cabinet->member_id,
        'service_plan_id' => $cabinet->service_plan_id,
        'team_id' => $cabinet->team_id,
    ]);
});
```
