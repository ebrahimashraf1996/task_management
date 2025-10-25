<?php

namespace App\Http\Requests\Api\User;

use App\Enums\User\UserRoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UserFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('viewAny', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'email' => ['sometimes', 'email'],
            'role' => ['sometimes', new Enum(UserRoleEnum::class)],
            'sort' => ['sometimes', 'in:desc,asc'],
            'per_page' => ['sometimes', 'numeric', 'integer', 'min:1'],
        ];
    }
}
