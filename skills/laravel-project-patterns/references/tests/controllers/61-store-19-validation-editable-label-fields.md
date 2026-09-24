# Store Tests: Validation Editable Label Fields

Pest POST store: Separate required-name case plus complete name/description/color dataset with defaults and data spread.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Team;
use Illuminate\Support\Str;

describe('store', function (): void {
    it('validates fields', function (array $data, array $errors): void {
        $team = Team::factory()->createOne();

        login(team: $team);

        $response = post(route('teams.item-groups.store', [
            'team' => $team,
        ]), [
            'name' => 'Electronics',
            ...$data,
        ]);

        $response->assertRedirectBackWithErrors($errors);
    })->with([
        'name maximum' => [
            'data' => ['name' => Str::repeat('a', 256)],
            'errors' => ['name' => 'The name field must not be greater than 255 characters.'],
        ],
        'description maximum' => [
            'data' => ['description' => Str::repeat('a', 2001)],
            'errors' => [
                'description' => 'The description field must not be greater than 2000 characters.',
            ],
        ],
        'hex color' => [
            'data' => ['color' => 'blue'],
            'errors' => ['color' => 'The color field must be a valid hexadecimal color.'],
        ],
    ]);

    it('requires a name', function (): void {
        $team = Team::factory()->createOne();

        login(team: $team);

        $response = post(route('teams.item-groups.store', [
            'team' => $team,
        ]));

        $response->assertRedirectBackWithErrors([
            'name' => 'The name field is required.',
        ]);
    });
});
```
