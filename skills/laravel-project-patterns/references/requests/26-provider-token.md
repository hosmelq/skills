# Requests: Validate Provider Token Input

One provider requires only id_token; the other also requires nonce and permits nullable names. These rules validate input shape. The authentication integration must verify token claims and nonce.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class GoogleAuthenticatedSessionRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'id_token' => ['required', 'string'],
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AppleAuthenticatedSessionRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'string', 'max:255'],
            'id_token' => ['required', 'string'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'nonce' => ['required', 'string'],
        ];
    }
}
```
