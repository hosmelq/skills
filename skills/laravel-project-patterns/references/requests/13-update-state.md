# Requests: Update an Initial State

After field validation, reject a supplied base state that would move an initial record away from Open. Skip the invariant when another field failed or base_status is absent; add a field error rather than a general error bag.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use function App\__;

use App\Enums\BaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Closure;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Stringable;

class UpdateWorkOrderStatusRequest extends FormRequest
{
    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || ! $this->has('base_status')) {
                    return;
                }

                $workOrderStatus = $this->route('work_order_status');

                assert($workOrderStatus instanceof WorkOrderStatus);

                if (
                    ! $workOrderStatus->is_initial
                    || $this->enum('base_status', BaseStatus::class) === BaseStatus::Open
                ) {
                    return;
                }

                $validator->errors()->add(
                    'base_status',
                    __('work_order_status.validation.initial_must_be_open'),
                );
            },
        ];
    }

    /**
     * @return array<string, list<string|Stringable>>
     */
    public function rules(
        #[RouteParameter('team')] Team $team,
        #[RouteParameter('work_order_status')] WorkOrderStatus $workOrderStatus
    ): array {
        return [
            'base_status' => [
                'sometimes',
                'required',
                Rule::enum(BaseStatus::class),
            ],
            'color' => ['sometimes', 'nullable', 'string', 'hex_color'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'is_member_visible' => ['sometimes', 'boolean'],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique(WorkOrderStatus::class)
                    ->ignore($workOrderStatus)
                    ->where('team_id', $team->id)
                    ->withoutTrashed(),
            ],
        ];
    }
}
```
