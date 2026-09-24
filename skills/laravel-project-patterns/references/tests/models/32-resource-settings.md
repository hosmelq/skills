# Model Tests: Settings Resource

Exact resource JSON for model settings: enum backing values, integers, true feature flags, a nationally formatted phone, timezone, slug, public ID and timestamps.

This resource uses national phone formatting; the explicit API wrapper uses E.164.

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

it('formats resource correctly', function (): void {
    $team = Team::factory()->createOne([
        'contact_email' => 'support@example.test',
        'contact_phone_number' => '415 555 0110',
        'country_code' => CountryCode::UnitedStates,
        'currency_code' => CurrencyCode::USD,
        'code_format_alphabet_type' => CodeAlphabet::Alphanumeric,
        'code_format_length' => 8,
        'code_format_prefix' => 'DEMO-',
        'assignment_mode' => AssignmentMode::Instant,
        'cabinets_enabled' => true,
        'name' => 'Demo Team',
        'work_orders_enabled' => true,
        'slug' => 'demo-team',
        'timezone' => 'America/Los_Angeles',
        'unit_system' => UnitSystem::Imperial,
        'weight_unit' => WeightUnit::Pounds,
    ]);

    $resource = json_decode($team->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'contact_email' => 'support@example.test',
        'contact_phone_number' => '(415) 555-0110',
        'country_code' => 'US',
        'created_at' => $team->created_at->toJSON(),
        'currency_code' => 'USD',
        'id' => $team->public_id,
        'code_format_alphabet_type' => 'alphanumeric',
        'code_format_length' => 8,
        'code_format_prefix' => 'DEMO-',
        'assignment_mode' => 'instant',
        'cabinets_enabled' => true,
        'name' => 'Demo Team',
        'work_orders_enabled' => true,
        'slug' => 'demo-team',
        'timezone' => 'America/Los_Angeles',
        'unit_system' => 'imperial',
        'updated_at' => $team->updated_at->toJSON(),
        'weight_unit' => 'pounds',
    ]);
});
```
