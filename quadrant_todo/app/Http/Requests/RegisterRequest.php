<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // register needs no token
    }

    public function rules(): array
    {
        return [
            'email' => [
                'bail',                // stop at the first failing rule
                'required',
                'string',
                'max:255',             // the column is VARCHAR(255)
                function (string $attribute, mixed $value, Closure $fail) {
                    if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                        $fail('Use a valid email address.');
                    }
                },
            ],
            'password' => ['bail', 'required', 'string', 'min:10', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'The email is required.',
            'password.required' => 'The password is required.',
            'password.min' => 'The password needs 10 to 200 characters.',
            'password.max' => 'The password needs 10 to 200 characters.',
        ];
    }
}