<?php

namespace App\Http\Requests\Api\Task;

use App\Enums\Task\TaskPriorityEnum;
use App\Enums\Task\TaskStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class TaskFilterRequest extends FormRequest
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
            'status' => ['sometimes', new Enum(TaskStatusEnum::class)],
            'priority' => ['sometimes', new Enum(TaskPriorityEnum::class)],
            'due_from' => ['sometimes', 'date'],
            'due_to' => ['sometimes', 'date'],
            'search' => ['sometimes', 'string'],
            'sort' => ['sometimes', 'in:desc,asc'],
            'per_page' => ['sometimes', 'numeric', 'integer', 'min:1'],
        ];
    }
}
