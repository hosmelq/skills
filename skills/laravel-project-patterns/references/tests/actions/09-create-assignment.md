# Action Tests: Create an Assignment

Integration tests for assignment creation with an existing owner and related parent: mock code generation, assert persisted foreign keys, normalized code and optional label, including required-only defaults.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\mock;

use App\Actions\Cabinets\CreateCabinet;
use App\Actions\Cabinets\Inputs\CreateCabinetInput;
use App\Actions\GenerateCabinetCode;
use App\Models\Cabinet;
use App\Models\Member;
use App\Models\ServicePlan;
use App\Models\Team;

it('creates a record', function (): void {
    $member = Member::factory()->createOne();
    $servicePlan = ServicePlan::factory()->recycle($member->team)->createOne();

    mock(GenerateCabinetCode::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (Team $team): bool => $team->is($member->team))
        ->andReturn('DEMO-008121');

    $cabinet = resolve(CreateCabinet::class)->handle(
        $member,
        CreateCabinetInput::from([
            'label' => 'Standard',
            'service_plan_id' => $servicePlan->id,
        ]),
    );

    expect($cabinet)->toBeInstanceOf(Cabinet::class);

    assertDatabaseHas(Cabinet::class, [
        'id' => $cabinet->id,
        'member_id' => $member->id,
        'team_id' => $member->team_id,
        'service_plan_id' => $servicePlan->id,
        'code' => 'DEMO-008121',
        'label' => 'Standard',
        'normalized_code' => 'DEMO008121',
    ]);
});

it('creates a record with only required fields', function (): void {
    $member = Member::factory()->createOne();
    $servicePlan = ServicePlan::factory()->recycle($member->team)->createOne();

    mock(GenerateCabinetCode::class)
        ->shouldReceive('handle')
        ->once()
        ->andReturn('DEMO-008122');

    $cabinet = resolve(CreateCabinet::class)->handle(
        $member,
        CreateCabinetInput::from([
            'service_plan_id' => $servicePlan->id,
        ]),
    );

    assertDatabaseHas(Cabinet::class, [
        'id' => $cabinet->id,
        'member_id' => $member->id,
        'team_id' => $member->team_id,
        'service_plan_id' => $servicePlan->id,
        'code' => 'DEMO-008122',
        'label' => null,
        'normalized_code' => 'DEMO008122',
    ]);
});
```
