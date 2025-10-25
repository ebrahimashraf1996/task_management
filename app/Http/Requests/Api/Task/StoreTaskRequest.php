<?php

namespace App\Http\Requests\Api\Task;

use App\Enums\Task\TaskPriorityEnum;
use App\Enums\Task\TaskStatusEnum;
use App\Enums\User\UserRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['required', 'date'],
            'status' => ['required', new Enum(TaskStatusEnum::class)],
            'priority' => ['required', new Enum(TaskPriorityEnum::class)],
            'user_id' => auth()->user()->isAdmin() ? ['required', Rule::exists('users', 'id')
                ->where('role', UserRoleEnum::User),
            ] : 'prohibited',
        ];
    }
}
