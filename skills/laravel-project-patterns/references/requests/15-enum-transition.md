# Requests: Restrict an Enum Transition

Allow only the two decision cases. Rule::enum()->only() constrains input; the action still checks whether the current record can transition.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EnrollmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class UpdateEnrollmentStatusRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable>>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(EnrollmentStatus::class)->only([
                    EnrollmentStatus::Approved,
                    EnrollmentStatus::Rejected,
                ]),
            ],
        ];
    }
}
```
