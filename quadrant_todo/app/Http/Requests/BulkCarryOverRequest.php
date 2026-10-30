<?php

namespace App\Http\Requests;

use App\Rules\RealDate;
use Illuminate\Foundation\Http\FormRequest;

class BulkCarryOverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_date' => ['bail', 'required', 'string', new RealDate()],
            'new_due_date' => ['sometimes', 'nullable', 'string', new RealDate()],
        ];
    }

    public function messages(): array
    {
        return [
            'from_date.required' => 'The from_date is required.',
        ];
    }
}