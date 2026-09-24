# Action Tests: Create Assignment Guards

Integration tests for assignment creation: reject a related parent from another tenant, an inactive parent and a duplicate owner/parent pair; propagate an unrelated unique-index violation. Assert failed writes as shown.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\mock;

use App\Actions\Cabinets\CreateCabinet;
use App\Actions\Cabinets\Inputs\CreateCabinetInput;
use App\Actions\GenerateCabinetCode;
use App\Exceptions\CannotCreateCabinet;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\Cabinet;
use App\Models\Member;
use App\Models\ServicePlan;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;

it('rejects parent records from another tenant', function (): void {
    $member = Member::factory()->createOne();
    $servicePlan = ServicePlan::factory()->createOne();

    mock(GenerateCabinetCode::class)
        ->shouldNotReceive('handle');

    expect(fn () => resolve(CreateCabinet::class)->handle(
        $member,
        CreateCabinetInput::from([
            'service_plan_id' => $servicePlan->id,
        ]),
    ))->toThrow(ModelNotFoundException::class);

    assertDatabaseMissing(Cabinet::class, [
        'member_id' => $member->id,
        'service_plan_id' => $servicePlan->id,
    ]);
});

it('rejects deactivated parent records', function (): void {
    $member = Member::factory()->createOne();
    $servicePlan = ServicePlan::factory()
        ->deactivated()
        ->recycle($member->team)
        ->createOne();

    mock(GenerateCabinetCode::class)
        ->shouldNotReceive('handle');

    expect(fn () => resolve(CreateCabinet::class)->handle(
        $member,
        CreateCabinetInput::from([
            'service_plan_id' => $servicePlan->id,
        ]),
    ))->toThrow(
        CannotUseDeactivatedServicePlan::class,
        'Cannot use a deactivated service plan.',
    );

    assertDatabaseMissing(Cabinet::class, [
        'member_id' => $member->id,
        'service_plan_id' => $servicePlan->id,
    ]);
});

it('rejects a parent record already assigned to the owner', function (): void {
    $cabinet = Cabinet::factory()->createOne();

    mock(GenerateCabinetCode::class)
        ->shouldReceive('handle')
        ->once()
        ->andReturn('DEMO-008123');

    expect(fn () => resolve(CreateCabinet::class)->handle(
        $cabinet->member,
        CreateCabinetInput::from([
            'service_plan_id' => $cabinet->service_plan_id,
        ]),
    ))->toThrow(
        CannotCreateCabinet::class,
        'A member can only have one cabinet per service plan.',
    );

    assertDatabaseCount(Cabinet::class, 1);
});

it('propagates unrelated unique constraint violations', function (): void {
    $existingCabinet = Cabinet::factory()->createOne([
        'code' => 'DEMO-008124',
        'normalized_code' => 'DEMO008124',
    ]);
    $member = Member::factory()->recycle($existingCabinet->team)->createOne();
    $servicePlan = ServicePlan::factory()->recycle($existingCabinet->team)->createOne();

    mock(GenerateCabinetCode::class)
        ->shouldReceive('handle')
        ->once()
        ->andReturn('DEMO-008124');

    expect(fn () => resolve(CreateCabinet::class)->handle(
        $member,
        CreateCabinetInput::from([
            'service_plan_id' => $servicePlan->id,
        ]),
    ))->toThrow(function (UniqueConstraintViolationException $exception): void {
        expect($exception->index)->toBe('cabinets_active_normalized_code_unique');
    });

    assertDatabaseMissing(Cabinet::class, [
        'member_id' => $member->id,
        'service_plan_id' => $servicePlan->id,
    ]);
});
```
