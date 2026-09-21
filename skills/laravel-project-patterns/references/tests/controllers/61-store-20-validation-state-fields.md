# Store Tests: Validation State Fields

Pest POST store: Complete enum/base-state, boolean visibility, color, name and description dataset.

## Validates fields

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Enums\WorkOrderBaseStatus;
use App\Models\Team;
use Illuminate\Support\Str;

describe('store', function (): void {
    it('validates fields', function (array $data, array $expected): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        $response = post(route('teams.work-order-statuses.store', [
            'team' => $team,
        ]), $data);

        $response->assertRedirectBackWithErrors($expected);
    })->with([
        'boolean' => [
            'data' => [
                'base_status' => WorkOrderBaseStatus::Received(),
                'is_member_visible' => 'invalid',
                'name' => 'Received',
            ],
            'expected' => [
                'is_member_visible' => 'The is member visible field must be true or false.',
            ],
        ],
        'enum' => [
            'data' => [
                'base_status' => 'invalid',
                'name' => 'Received',
            ],
            'expected' => [
                'base_status' => 'The selected base status is invalid.',
            ],
        ],
        'hex color' => [
            'data' => [
                'base_status' => WorkOrderBaseStatus::Received(),
                'color' => 'blue',
                'name' => 'Received',
            ],
            'expected' => [
                'color' => 'The color field must be a valid hexadecimal color.',
            ],
        ],
        'max:255 (string)' => [
            'data' => [
                'base_status' => WorkOrderBaseStatus::Received(),
                'name' => Str::repeat('a', 256),
            ],
            'expected' => [
                'name' => 'The name field must not be greater than 255 characters.',
            ],
        ],
        'max:2000 (string)' => [
            'data' => [
                'base_status' => WorkOrderBaseStatus::Received(),
                'description' => Str::repeat('a', 2001),
                'name' => 'Received',
            ],
            'expected' => [
                'description' => 'The description field must not be greater than 2000 characters.',
            ],
        ],
        'required' => [
            'data' => [],
            'expected' => [
                'base_status' => 'The base status field is required.',
                'name' => 'The name field is required.',
            ],
        ],
    ]);
});
```
