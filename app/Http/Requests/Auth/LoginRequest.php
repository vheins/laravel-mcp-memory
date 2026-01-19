<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data.type' => ['required', 'in:auth_login'],
            'data.attributes.email' => ['required', 'string', 'email'],
            'data.attributes.password' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'data.attributes.email' => 'email',
            'data.attributes.password' => 'password',
        ];
    }
}
