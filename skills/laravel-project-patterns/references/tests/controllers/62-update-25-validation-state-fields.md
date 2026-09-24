# Update Tests: Validation State Fields

PATCH update: Whole state-fields dataset, plus invalid initial enum producing only its enum error and rejection of changing the initial state.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrderStatuses\UpdateWorkOrderStatus;
use App\Enums\WorkOrderBaseStatus;
use App\Models\WorkOrderStatus;
use Illuminate\Support\Str;

describe('update', function (): void {
    it('validates fields', function (array $data, array $expected): void {
        $workOrderStatus = WorkOrderStatus::factory()->createOne();

        login(team: $workOrderStatus->team);

        $response = patch(route('teams.work-order-statuses.update', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]), $data);

        $response->assertRedirectBackWithErrors($expected);
    })->with([
        'boolean' => [
            'data' => [
                'is_member_visible' => 'invalid',
            ],
            'expected' => [
                'is_member_visible' => 'The is member visible field must be true or false.',
            ],
        ],
        'enum' => [
            'data' => [
                'base_status' => 'invalid',
            ],
            'expected' => [
                'base_status' => 'The selected base status is invalid.',
            ],
        ],
        'hex color' => [
            'data' => [
                'color' => 'blue',
            ],
            'expected' => [
                'color' => 'The color field must be a valid hexadecimal color.',
            ],
        ],
        'max:255 (string)' => [
            'data' => [
                'name' => Str::repeat('a', 256),
            ],
            'expected' => [
                'name' => 'The name field must not be greater than 255 characters.',
            ],
        ],
        'max:2000 (string)' => [
            'data' => [
                'description' => Str::repeat('a', 2001),
            ],
            'expected' => [
                'description' => 'The description field must not be greater than 2000 characters.',
            ],
        ],
        'sometimes (required)' => [
            'data' => [
                'base_status' => '',
                'name' => '',
            ],
            'expected' => [
                'base_status' => 'The base status field is required.',
                'name' => 'The name field is required.',
            ],
        ],
    ]);

    it('returns only the enum error for an invalid initial base status', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->initial()->createOne();

        login(team: $workOrderStatus->team);

        $response = patch(route('teams.work-order-statuses.update', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]), [
            'base_status' => 'invalid',
        ]);

        $response->assertRedirectBackWithErrors([
            'base_status' => 'The selected base status is invalid.',
        ]);

        $baseStatusErrors = session('errors')->getBag('default')->get('base_status');

        expect($baseStatusErrors)->toBe([
            'The selected base status is invalid.',
        ]);
    });

    it('prevents changing the initial base status', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->initial()->createOne();

        login(team: $workOrderStatus->team);

        mock(UpdateWorkOrderStatus::class)
            ->shouldNotReceive('handle');

        $response = patch(route('teams.work-order-statuses.update', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]), [
            'base_status' => WorkOrderBaseStatus::Exception(),
        ]);

        $response->assertRedirectBackWithErrors([
            'base_status' => 'The initial work order status must use the received base status.',
        ]);
    });
});
```
