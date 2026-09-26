# Requests: Scoped Unique Name

Scope uniqueness to the bound tenant, exclude soft-deleted rows and ignore the bound model on update. Keep rule order; database collation and indexes determine case sensitivity.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\ItemGroup;
use App\Models\Team;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class StoreItemGroupRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable>>
     */
    public function rules(#[RouteParameter('team')] Team $team): array
    {
        return [
            'color' => ['nullable', 'string', 'hex_color'],
            'description' => ['nullable', 'string', 'max:2000'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(ItemGroup::class)
                    ->where('team_id', $team->id)
                    ->withoutTrashed(),
            ],
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\ItemGroup;
use App\Models\Team;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class UpdateItemGroupRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable>>
     */
    public function rules(
        #[RouteParameter('team')] Team $team,
        #[RouteParameter('item_group')] ItemGroup $itemGroup
    ): array {
        return [
            'color' => ['sometimes', 'nullable', 'string', 'hex_color'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique(ItemGroup::class)
                    ->ignore($itemGroup)
                    ->where('team_id', $team->id)
                    ->withoutTrashed(),
            ],
        ];
    }
}
```
