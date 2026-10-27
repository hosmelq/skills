<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class AppleAuthenticatedSessionRequest extends FormRequest
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
