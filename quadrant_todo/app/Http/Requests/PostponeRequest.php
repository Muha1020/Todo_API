<?php

namespace App\Http\Requests;

use App\Rules\RealDate;
use Illuminate\Foundation\Http\FormRequest;

class PostponeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Required: an absent date answers 400.
        return [
            'new_due_date' => ['bail', 'required', 'string', new RealDate()],
        ];
    }

    public function messages(): array
    {
        return [
            'new_due_date.required' => 'The new_due_date is required.',
        ];
    }
}