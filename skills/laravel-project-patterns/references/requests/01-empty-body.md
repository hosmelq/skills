# Requests: Empty Body

Return no field rules for an operation without a payload. Authorization and lifecycle guards remain in the inspected route, policy and action.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyServicePlanRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [];
    }
}
```
