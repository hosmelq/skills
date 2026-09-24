# Model Tests: Account and Current Tenant Resource

Exact account resource JSON with a normalized-email avatar hash and full current-tenant payload; a separate case asserts null when the user has no current tenant.

The fictional team factory defaults to US, USD, America/Los_Angeles, imperial units and pounds. Match the inspected factory defaults or set the corresponding attributes explicitly.

```php
<?php

declare(strict_types=1);

use App\Enums\AssignmentMode;
use App\Models\Team;
use App\Models\User;

it('formats resource correctly', function (): void {
    $team = Team::factory()->createOne([
        'contact_email' => 'support@example.test',
        'contact_phone_number' => '415 555 0114',
        'code_format_prefix' => 'DEMO-',
        'assignment_mode' => AssignmentMode::Instant,
        'cabinets_enabled' => true,
        'name' => 'Current Demo Team',
        'work_orders_enabled' => true,
        'slug' => 'current-demo-team',
    ]);

    $user = User::factory()->withTeam($team)->createOne([
        'email' => ' TEST@example.com ',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
    ]);

    $resource = json_decode($user->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'avatar_url' => sprintf(
            'https://www.gravatar.com/avatar/%s?d=mp',
            '973dfe463ec85785f5f95af5ba3906eedb2d931c24e69824a89ea65dba4e813b'
        ),
        'created_at' => $user->created_at->toJSON(),
        'current_team' => [
            'contact_email' => 'support@example.test',
            'contact_phone_number' => '(415) 555-0114',
            'country_code' => 'US',
            'created_at' => $team->created_at->toJSON(),
            'currency_code' => 'USD',
            'id' => $team->public_id,
            'code_format_alphabet_type' => 'alphanumeric',
            'code_format_length' => Team::DEFAULT_CODE_LENGTH,
            'code_format_prefix' => 'DEMO-',
            'assignment_mode' => 'instant',
            'cabinets_enabled' => true,
            'name' => 'Current Demo Team',
            'work_orders_enabled' => true,
            'slug' => 'current-demo-team',
            'timezone' => 'America/Los_Angeles',
            'unit_system' => 'imperial',
            'updated_at' => $team->updated_at->toJSON(),
            'weight_unit' => 'pounds',
        ],
        'email' => $user->email,
        'first_name' => 'Jane',
        'id' => $user->public_id,
        'is_email_verified' => true,
        'last_name' => 'Doe',
        'name' => 'Jane Doe',
        'updated_at' => $user->updated_at->toJSON(),
    ]);
});

it('includes a null current tenant when none is assigned', function (): void {
    $user = User::factory()->createOne();

    $resource = json_decode($user->toResource()->toJson(), true);

    expect($resource['current_team'])->toBeNull();
});
```
