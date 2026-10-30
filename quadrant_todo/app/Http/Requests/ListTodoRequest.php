<?php

namespace App\Http\Requests;

use App\Enums\Quadrant;
use App\Rules\RealDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListTodosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // request format and ruling for the list route 
        return [
            'quadrant' => ['sometimes', 'nullable', 'string', Rule::in(Quadrant::values())],
            'status' => ['sometimes', 'nullable', 'string', Rule::in(['pending', 'completed'])],
            'due_date' => ['sometimes', 'nullable', 'string', new RealDate()],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'quadrant.in' => 'Use one of the four keys.',
            'status.in' => 'Use pending or completed.',
            'page.integer' => 'The page must be a whole number.',
            'page.min' => 'The page starts at 1.',
            'per_page.integer' => 'The per_page value must be a whole number.',
            'per_page.min' => 'The per_page value needs 1 to 100.',
            'per_page.max' => 'The per_page value needs 1 to 100.',
        ];
    }
}