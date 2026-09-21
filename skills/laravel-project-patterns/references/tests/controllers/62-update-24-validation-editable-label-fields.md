# Update Tests: Validation Editable Label Fields

Pest PATCH update: Whole editable name/description/color dataset; update omissions and explicit values are preserved.

## Validates fields

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\ItemGroup;
use Illuminate\Support\Str;

describe('update', function (): void {
    it('validates fields', function (array $data, array $errors): void {
        $workOrderItemGroup = ItemGroup::factory()->createOne();

        signIn(team: $workOrderItemGroup->team);

        $response = patch(route('teams.item-groups.update', [
            'team' => $workOrderItemGroup->team,
            'item_group' => $workOrderItemGroup,
        ]), $data);

        $response->assertRedirectBackWithErrors($errors);
    })->with([
        'required name' => [
            'data' => ['name' => null],
            'errors' => ['name' => 'The name field is required.'],
        ],
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
});
```
