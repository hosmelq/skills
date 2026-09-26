# Requests: Create an Ordered State

Require the base enum and tenant-unique name. Optional visibility is boolean; nullable color and description remain separate from required fields.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\BaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class StoreWorkOrderStatusRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable>>
     */
    public function rules(#[RouteParameter('team')] Team $team): array
    {
        return [
            'base_status' => ['required', Rule::enum(BaseStatus::class)],
            'color' => ['nullable', 'string', 'hex_color'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_member_visible' => ['sometimes', 'boolean'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(WorkOrderStatus::class)
                    ->where('team_id', $team->id)
                    ->withoutTrashed(),
            ],
        ];
    }
}
```
