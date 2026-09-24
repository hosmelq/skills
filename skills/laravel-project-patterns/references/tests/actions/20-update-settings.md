# Action Tests: Update Tenant Settings

Integration tests for tenant setting updates: full input, omitted-field preservation and explicit-null clearing, including contact fields, enums, code format, timezone and boolean flags.

This `RawPhoneNumberCast` stores null as `''` and reads it back as null; assert an empty string only when the inspected cast does that.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\Teams\Inputs\UpdateTeamInput;
use App\Actions\Teams\UpdateTeam;
use App\Enums\AssignmentMode;
use App\Enums\CodeAlphabet;
use App\Enums\CountryCode;
use App\Enums\UnitSystem;
use App\Enums\WeightUnit;
use App\Models\Team;

it('updates a record', function (): void {
    $team = Team::factory()->createOne([
        'name' => 'My Team',
        'work_orders_enabled' => true,
    ]);
    $alphabetType = CodeAlphabet::Alphanumeric();

    $updatedTeam = resolve(UpdateTeam::class)->handle(
        team: $team,
        input: UpdateTeamInput::from([
            'assignment_mode' => AssignmentMode::Instant(),
            'cabinets_enabled' => true,
            'code_format_alphabet_type' => $alphabetType,
            'code_format_length' => 4,
            'code_format_prefix' => 'DEMO-',
            'contact_email' => 'alex@example.net',
            'contact_phone_number' => '(415) 555-0110',
            'country_code' => CountryCode::Canada(),
            'name' => 'Updated Team',
            'timezone' => 'America/Vancouver',
            'unit_system' => UnitSystem::Imperial(),
            'weight_unit' => WeightUnit::Grams(),
            'work_orders_enabled' => false,
        ]),
    );

    expect($updatedTeam->is($team))->toBeTrue();

    assertDatabaseHas(Team::class, [
        'id' => $team->id,
        'owner_id' => $team->owner_id,
        'assignment_mode' => AssignmentMode::Instant,
        'cabinets_enabled' => true,
        'code_format_alphabet_type' => $alphabetType,
        'code_format_length' => 4,
        'code_format_prefix' => 'DEMO-',
        'contact_email' => 'alex@example.net',
        'contact_phone_number' => '(415) 555-0110',
        'country_code' => CountryCode::Canada,
        'name' => 'Updated Team',
        'timezone' => 'America/Vancouver',
        'unit_system' => UnitSystem::Imperial,
        'weight_unit' => WeightUnit::Grams,
        'work_orders_enabled' => false,
    ]);
});

it('updates only provided fields', function (): void {
    $team = Team::factory()->createOne([
        'cabinets_enabled' => true,
        'work_orders_enabled' => true,
    ]);

    resolve(UpdateTeam::class)->handle(
        team: $team,
        input: UpdateTeamInput::from([
            'cabinets_enabled' => false,
            'work_orders_enabled' => false,
        ]),
    );

    assertDatabaseHas(Team::class, [
        'id' => $team->id,
        'cabinets_enabled' => false,
        'name' => $team->name,
        'work_orders_enabled' => false,
    ]);
});

it('clears nullable fields', function (): void {
    $team = Team::factory()->createOne([
        'code_format_prefix' => 'DEMO-',
        'contact_email' => 'ops@example.com',
        'contact_phone_number' => '(415) 555-0110',
    ]);

    resolve(UpdateTeam::class)->handle(
        team: $team,
        input: UpdateTeamInput::from([
            'code_format_prefix' => null,
            'contact_email' => null,
            'contact_phone_number' => null,
        ]),
    );

    assertDatabaseHas(Team::class, [
        'id' => $team->id,
        'code_format_prefix' => null,
        'contact_email' => null,
        'contact_phone_number' => '',
    ]);

    $team->refresh();

    expect($team->contact_phone_number)->toBeNull();
});
```
