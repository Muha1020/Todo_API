<?php

namespace App\Http\Requests;

use App\Rules\RealDate;
use Illuminate\Foundation\Http\FormRequest;

class CarryOverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Optional. An absent or empty value means "today", decided in the service.
        return [
            'new_due_date' => ['sometimes', 'nullable', 'string', new RealDate()],
        ];
    }
}