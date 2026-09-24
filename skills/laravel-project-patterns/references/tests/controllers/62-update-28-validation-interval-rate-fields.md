# Update Tests: Validation Interval Rate Fields

PATCH update: Whole interval/rate dataset with the original upper/lower bound constraints and explicit negative action expectation.

## Validates fields

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ServicePlans\UpdatePlanRate;
use App\Models\PlanRate;
use Illuminate\Support\Str;

describe('update', function (): void {
    it('validates fields', function (array $data, array $expected): void {
        $rate = PlanRate::factory()->createOne();

        login(team: $rate->planRule->servicePlan->team);

        mock(UpdatePlanRate::class)
            ->shouldNotReceive('handle');

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]), $data);

        $response->assertRedirectBackWithErrors($expected);
    })->with([
        'decimal:0,2' => [
            'data' => [
                'rate' => 12.345,
            ],
            'expected' => [
                'rate' => 'The rate field must have 0-2 decimal places.',
            ],
        ],
        'decimal:0,4' => [
            'data' => [
                'minimum_weight' => 12.12345,
                'maximum_weight' => 15.12345,
            ],
            'expected' => [
                'minimum_weight' => 'The minimum weight field must have 0-4 decimal places.',
                'maximum_weight' => 'The maximum weight field must have 0-4 decimal places.',
            ],
        ],
        'gt:minimum_weight' => [
            'data' => [
                'minimum_weight' => 10,
                'maximum_weight' => 10,
            ],
            'expected' => [
                'maximum_weight' => 'The maximum weight field must be greater than 10.',
            ],
        ],
        'gte:0' => [
            'data' => [
                'minimum_weight' => -1,
                'rate' => -1,
            ],
            'expected' => [
                'minimum_weight' => 'The minimum weight field must be greater than or equal to 0.',
                'rate' => 'The rate field must be greater than or equal to 0.',
            ],
        ],
        'lte:maximum_weight' => [
            'data' => [
                'minimum_weight' => 11,
                'maximum_weight' => 10,
            ],
            'expected' => [
                'minimum_weight' => 'The minimum weight field must be less than or equal to 10.',
                'maximum_weight' => 'The maximum weight field must be greater than 11.',
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
        'sometimes (required)' => [
            'data' => [
                'name' => '',
                'minimum_weight' => '',
                'maximum_weight' => '',
                'rate' => '',
            ],
            'expected' => [
                'name' => 'The name field is required.',
                'minimum_weight' => 'The minimum weight field is required.',
                'rate' => 'The rate field is required.',
            ],
        ],
    ]);
});
```
