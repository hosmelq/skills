# Action Tests: Create and Select a Tenant

Integration tests for tenant creation: settings and defaults, owner membership, current tenant selection and a persisted initial status. Assert each side effect independently of the returned model.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\Teams\CreateTeam;
use App\Actions\Teams\Inputs\CreateTeamInput;
use App\Enums\AssignmentMode;
use App\Enums\BaseStatus;
use App\Enums\CodeAlphabet;
use App\Enums\CountryCode;
use App\Enums\UnitSystem;
use App\Enums\WeightUnit;
use App\Models\Team;
use App\Models\User;
use App\Models\WorkOrderStatus;

it('creates a tenant and selects it', function (): void {
    $user = User::factory()->createOne();
    $alphabetType = CodeAlphabet::Numbers();

    $team = resolve(CreateTeam::class)->handle(
        user: $user,
        input: CreateTeamInput::from([
            'assignment_mode' => AssignmentMode::Instant(),
            'cabinets_enabled' => true,
            'code_format_alphabet_type' => $alphabetType,
            'code_format_length' => 8,
            'code_format_prefix' => 'DEMO-',
            'contact_email' => 'ops@example.com',
            'contact_phone_number' => '(415) 555-0110',
            'country_code' => CountryCode::UnitedStates(),
            'name' => 'My Team',
            'timezone' => 'America/Los_Angeles',
            'unit_system' => UnitSystem::Metric(),
            'weight_unit' => WeightUnit::Kilograms(),
            'work_orders_enabled' => true,
        ]),
    );

    expect($team)->toBeInstanceOf(Team::class);

    assertDatabaseHas(Team::class, [
        'id' => $team->id,
        'owner_id' => $user->id,
        'assignment_mode' => AssignmentMode::Instant,
        'cabinets_enabled' => true,
        'code_format_alphabet_type' => $alphabetType,
        'code_format_length' => 8,
        'code_format_prefix' => 'DEMO-',
        'contact_email' => 'ops@example.com',
        'contact_phone_number' => '(415) 555-0110',
        'country_code' => CountryCode::UnitedStates,
        'name' => 'My Team',
        'timezone' => 'America/Los_Angeles',
        'unit_system' => UnitSystem::Metric,
        'weight_unit' => WeightUnit::Kilograms,
        'work_orders_enabled' => true,
    ]);

    assertDatabaseHas(WorkOrderStatus::class, [
        'team_id' => $team->id,

        'base_status' => BaseStatus::Open,
        'is_initial' => true,
        'is_member_visible' => false,
        'name' => 'Open',
    ]);

    $user->refresh();

    expect($user)
        ->current_team_id->toBe($team->id)
        ->belongsToTeam($team)->toBeTrue()
        ->and($team)
        ->owner->is($user)->toBeTrue()
        ->users->toHaveCount(1);
});

it('creates a record with only required fields', function (): void {
    $user = User::factory()->createOne();

    $team = resolve(CreateTeam::class)->handle(
        user: $user,
        input: CreateTeamInput::from([
            'country_code' => CountryCode::UnitedStates(),
            'name' => 'Default Team',
            'timezone' => 'America/Los_Angeles',
        ]),
    );

    assertDatabaseHas(Team::class, [
        'id' => $team->id,
        'assignment_mode' => AssignmentMode::RequiresApproval,
        'cabinets_enabled' => false,
        'code_format_alphabet_type' => CodeAlphabet::Alphanumeric,
        'code_format_length' => Team::DEFAULT_CODE_LENGTH,
        'code_format_prefix' => null,
        'contact_email' => null,
        'contact_phone_number' => null,
        'unit_system' => UnitSystem::Imperial,
        'weight_unit' => WeightUnit::Pounds,
        'work_orders_enabled' => false,
    ]);
});
```
