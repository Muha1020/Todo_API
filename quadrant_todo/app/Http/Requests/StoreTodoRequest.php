<?php

namespace App\Http\Requests;

use App\Enums\Quadrant;
use App\Rules\RealDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // the token middleware already ran
    }

    public function rules(): array
    {
        return [
            'title' => ['bail', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'quadrant' => ['bail', 'required', 'string', Rule::in(Quadrant::values())],
            'due_date' => ['bail', 'required', 'string', new RealDate()],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title is required.',
            'title.max' => 'The title needs 1 to 255 characters.',
            'description.max' => 'The description allows up to 5000 characters.',
            'quadrant.required' => 'The quadrant is required.',
            'quadrant.in' => 'Use one of the four keys.',
            'due_date.required' => 'The due date is required.',
        ];
    }
}