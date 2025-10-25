<?php

namespace App\Http\Requests\Api\AuditLog;

use Illuminate\Foundation\Http\FormRequest;

class AuditLogFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sort' => ['sometimes', 'in:desc,asc'],
            'per_page' => ['sometimes', 'numeric', 'integer', 'min:1'],
        ];
    }
}
