# Requests: Create and Update an Assignment

Creation scopes the selected parent to the owner tenant and excludes trashed rows; its active-state check belongs to the action. Update permits a nullable label and prohibits a nonempty replacement ID. prohibited allows empty values; use missing only when presence itself must fail.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Member;
use App\Models\ServicePlan;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class StoreCabinetRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable|ValidationRule>>
     */
    public function rules(#[RouteParameter('member')] Member $member): array
    {
        return [
            'label' => ['nullable', 'string', 'max:255'],
            'service_plan_id' => [
                'required',
                Rule::exists(ServicePlan::class, 'id')
                    ->where('team_id', $member->team_id)
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

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Stringable;

class UpdateCabinetRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:255'],
            'service_plan_id' => ['prohibited'],
        ];
    }
}
```
