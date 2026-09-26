# Requests: Validate Email and One-Time Code

Request a code with strict DNS email and the installed indisposable rule. Login additionally requires six digits and an existing code. Existence does not establish email ownership, expiry or one-time consumption; the authentication flow checks those contracts.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class EmailOtpRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'max:255',
                'email:strict,dns',
                'indisposable',
            ],
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\OneTimePassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class EmailOtpLoginRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable>>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'digits:6',
                Rule::exists(OneTimePassword::class),
            ],
            'email' => [
                'required',
                'string',
                'max:255',
                'email:strict,dns',
                'indisposable',
            ],
        ];
    }
}
```
