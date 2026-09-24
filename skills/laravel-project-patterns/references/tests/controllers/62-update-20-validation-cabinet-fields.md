# Update Tests: Validation Cabinet Fields

PATCH update: Label validation dataset; `service_plan_id` is prohibited on the existing nested record, and the dataset rejects a non-empty value.

## Validates fields

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Cabinet;
use Illuminate\Support\Str;

describe('update', function (): void {
    it('validates fields', function (array $data, array $expected): void {
        $cabinet = Cabinet::factory()->createOne();

        login(team: $cabinet->member->team);

        $response = patch(route('teams.members.cabinets.update', [
            'team' => $cabinet->member->team,
            'member' => $cabinet->member,
            'cabinet' => $cabinet,
        ]), $data);

        $response->assertRedirectBackWithErrors($expected);
    })->with([
        'max:255 (string)' => [
            'data' => [
                'label' => Str::repeat('a', 256),
            ],
            'expected' => [
                'label' => 'The label field must not be greater than 255 characters.',
            ],
        ],
        'prohibited' => [
            'data' => [
                'service_plan_id' => 'service-plan-id',
            ],
            'expected' => [
                'service_plan_id' => 'The service plan id field is prohibited.',
            ],
        ],
    ]);
});
```
