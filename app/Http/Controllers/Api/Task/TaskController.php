<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Task\StoreTaskRequest;
use App\Http\Requests\Api\Task\TaskFilterRequest;
use App\Http\Requests\Api\Task\UpdateTaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    protected TaskService $service;

    public function __construct(TaskService $service)
    {
        $this->service = $service;
    }

    public function index(TaskFilterRequest $request): JsonResponse
    {
        try {

            $data = $this->service->getTasks($request->validated());

            return response()->success($data, 'Tasks List');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }
    }

    public function store(StoreTaskRequest $request)
    {
        try {

            $data = $this->service->createTask($request->validated());

            return response()->success($data, 'Task Created Successfully');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }
    }

    public function update(Task $task, UpdateTaskRequest $request): JsonResponse
    {
        try {

            $data = $this->service->updateTask($task, $request->validated());

            return response()->success($data, 'Task Updated Successfully');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }
    }

    public function destroy(Task $task): JsonResponse
    {
        try {
            if (! auth()->user()->can('delete', $task)) {
                return response()->error([], 'You are not authorized to perform this action.', 403);
            }

            $this->service->deleteTask($task);

            return response()->success([], 'Task Deleted Successfully');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }
    }
}
