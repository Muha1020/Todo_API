<?php

namespace App\Http\Requests;

use App\Enums\Quadrant;
use App\Rules\RealDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // 'sometimes' means: check the field only if the client sent it.
        return [
            'title' => ['sometimes', 'bail', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'quadrant' => ['sometimes', 'bail', 'required', 'string', Rule::in(Quadrant::values())],
            'status' => ['sometimes', 'bail', 'required', 'string', Rule::in(['pending', 'completed'])],
            'due_date' => ['sometimes', 'bail', 'required', 'string', new RealDate()],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title cannot be empty.',
            'title.max' => 'The title needs 1 to 255 characters.',
            'description.max' => 'The description allows up to 5000 characters.',
            'quadrant.required' => 'The quadrant cannot be empty.',
            'quadrant.in' => 'Use one of the four keys.',
            'status.required' => 'The status cannot be empty.',
            'status.in' => 'Use pending or completed.',
            'due_date.required' => 'The due date cannot be empty.',
        ];
    }
}