# Model Tests: Settings Casts and Defaults

In-memory settings casts and defaults: exact enum cases, enum types, booleans, integers and a model constant. Also covers false flags and an initial enum status.

Preserve each inspected default; a default is checked on `new Model()`, not a factory that may override it.

```php
<?php

declare(strict_types=1);

use App\Enums\AssignmentMode;
use App\Enums\CodeAlphabet;
use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Enums\UnitSystem;
use App\Enums\WeightUnit;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Propaganistas\LaravelPhone\PhoneNumber;

it('correctly casts attributes', function (): void {
    $team = new Team([
        'assignment_mode' => 'instant',
        'cabinets_enabled' => 1,
        'code_format_alphabet_type' => 'alphanumeric',
        'code_format_length' => 4,
        'contact_phone_number' => '+1 415 555 0110',
        'country_code' => 'US',
        'created_at' => '2026-01-15 02:53:10',
        'currency_code' => 'USD',
        'unit_system' => 'imperial',
        'updated_at' => '2026-01-15 02:53:10',
        'weight_unit' => 'pounds',
        'work_orders_enabled' => 1,
    ]);

    expect($team)
        ->assignment_mode->toBeInstanceOf(AssignmentMode::class)
        ->cabinets_enabled->toBeBool()
        ->code_format_alphabet_type->toBeInstanceOf(CodeAlphabet::class)
        ->code_format_length->toBeInt()
        ->contact_phone_number->toBeInstanceOf(PhoneNumber::class)
        ->country_code->toBe(CountryCode::UnitedStates)
        ->created_at->toBeInstanceOf(CarbonImmutable::class)
        ->currency_code->toBe(CurrencyCode::USD)
        ->unit_system->toBeInstanceOf(UnitSystem::class)
        ->updated_at->toBeInstanceOf(CarbonImmutable::class)
        ->weight_unit->toBeInstanceOf(WeightUnit::class)
        ->work_orders_enabled->toBeBool();
});

it('sets model defaults', function (): void {
    $team = new Team();

    expect($team)
        ->assignment_mode->toBe(AssignmentMode::RequiresApproval)
        ->cabinets_enabled->toBeFalse()
        ->code_format_alphabet_type->toBe(CodeAlphabet::Alphanumeric)
        ->code_format_length->toBe(Team::DEFAULT_CODE_LENGTH)
        ->currency_code->toBe(CurrencyCode::USD)
        ->unit_system->toBe(UnitSystem::Imperial)
        ->weight_unit->toBe(WeightUnit::Pounds)
        ->work_orders_enabled->toBeFalse();
});
```

A single false default uses the same canonical name in its own model test file.

```php
<?php

declare(strict_types=1);

use App\Models\MemberAddress;

it('sets model defaults', function (): void {
    $address = new MemberAddress();

    expect($address)
        ->is_default->toBeFalse();
});
```

An enum default uses the same canonical name in its own model test file.

```php
<?php

declare(strict_types=1);

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;

it('sets model defaults', function (): void {
    $enrollment = new Enrollment();

    expect($enrollment)
        ->status->toBe(EnrollmentStatus::Pending);
});
```
