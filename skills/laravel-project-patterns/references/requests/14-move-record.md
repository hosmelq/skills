# Requests: Validate a Predecessor

Accept null to move to the start. A supplied predecessor must differ from the bound record, belong to the tenant and exclude trashed rows. The same form applies to other ordered models after replacing their bound type and route parameter.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Team;
use App\Models\WorkOrderStatus;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class MoveWorkOrderStatusRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable>>
     */
    public function rules(
        #[RouteParameter('team')] Team $team,
        #[RouteParameter('work_order_status')] WorkOrderStatus $workOrderStatus
    ): array {
        return [
            'move_after_id' => [
                'nullable',
                Rule::notIn([$workOrderStatus->id]),
                Rule::exists(WorkOrderStatus::class, 'id')
                    ->where('team_id', $team->id)
                    ->withoutTrashed(),
            ],
        ];
    }
}
```
