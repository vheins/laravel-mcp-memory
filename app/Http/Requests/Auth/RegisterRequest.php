<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function attributes(): array
    {
        return [
            'data.attributes.email' => 'email',
            'data.attributes.password' => 'password',
            'data.attributes.full_name' => 'full name',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data.type' => ['required', 'in:auth_register'],
            'data.attributes.email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'data.attributes.password' => ['required', 'string', Password::defaults()],
            'data.attributes.full_name' => ['required', 'string', 'max:255'],
        ];
    }
}
