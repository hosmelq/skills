<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\OneTimePassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

final class EmailOtpLoginRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'digits:6', Rule::exists(OneTimePassword::class)],
            'email' => ['required', 'string', 'max:255', 'email:strict,dns'],
        ];
    }
}
