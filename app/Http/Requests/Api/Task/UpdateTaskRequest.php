<?php

namespace App\Http\Requests\Api\Task;

use App\Enums\Task\TaskPriorityEnum;
use App\Enums\Task\TaskStatusEnum;
use App\Enums\User\UserRoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('update', $this->task);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'due_date' => ['sometimes', 'date'],
            'status' => ['sometimes', new Enum(TaskStatusEnum::class)],
            'priority' => ['sometimes', new Enum(TaskPriorityEnum::class)],
            'user_id' => \Auth::user()->isAdmin() ? ['sometimes', Rule::exists('users', 'id')
                ->where('role', UserRoleEnum::User),
            ] : 'prohibited',
        ];
    }
}
