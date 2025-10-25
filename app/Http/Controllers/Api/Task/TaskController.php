<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Task\StoreTaskRequest;
use App\Http\Requests\Api\Task\TaskFilterRequest;
use App\Http\Requests\Api\Task\UpdateTaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class TaskController extends Controller
{
    protected TaskService $service;

    public function __construct(TaskService $service)
    {
        $this->service = $service;
    }

    #[OA\Get(
        path: '/tasks',
        summary: 'Get list of tasks',
        security: [['bearerAuth' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'integer', enum: [1, 2, 3]), description: 'Filter by status (1=Pending, 2=InProgress, 3=Done)'),
            new OA\Parameter(name: 'priority', in: 'query', required: false, schema: new OA\Schema(type: 'integer', enum: [1, 2, 3]), description: 'Filter by priority (1=Low, 2=Medium, 3=High)'),
            new OA\Parameter(name: 'due_from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date'), description: 'Filter tasks due from this date'),
            new OA\Parameter(name: 'due_to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date'), description: 'Filter tasks due until this date'),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Search in title and description'),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc']), description: 'Sort order'),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1), description: 'Items per page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tasks list retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Tasks List'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'title', type: 'string', example: 'Complete project documentation'),
                                            new OA\Property(property: 'description', type: 'string', example: 'Write comprehensive documentation for the API'),
                                            new OA\Property(property: 'status', type: 'integer', example: 1),
                                            new OA\Property(property: 'priority', type: 'integer', example: 3),
                                            new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2025-11-01'),
                                            new OA\Property(property: 'user_id', type: 'integer', example: 1),
                                        ]
                                    )
                                ),
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 50),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Server error'),
        ]
    )]
    public function index(TaskFilterRequest $request): JsonResponse
    {
        try {

            $data = $this->service->getTasks($request->validated());

            return response()->success($data, 'Tasks List');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: '/tasks',
        summary: 'Create a new task',
        security: [['bearerAuth' => []]],
        tags: ['Tasks'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'description', 'due_date', 'status', 'priority'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Complete project documentation'),
                    new OA\Property(property: 'description', type: 'string', example: 'Write comprehensive documentation for the API endpoints'),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2025-11-01'),
                    new OA\Property(property: 'status', type: 'integer', enum: [1, 2, 3], example: 1, description: '1=Pending, 2=InProgress, 3=Done'),
                    new OA\Property(property: 'priority', type: 'integer', enum: [1, 2, 3], example: 3, description: '1=Low, 2=Medium, 3=High'),
                    new OA\Property(property: 'user_id', type: 'integer', example: 2, description: 'User ID (Admin only - assigns task to specific user)'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Task created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Task Created Successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'title', type: 'string', example: 'Complete project documentation'),
                                new OA\Property(property: 'description', type: 'string', example: 'Write comprehensive documentation for the API endpoints'),
                                new OA\Property(property: 'status', type: 'integer', example: 1),
                                new OA\Property(property: 'priority', type: 'integer', example: 3),
                                new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2025-11-01'),
                                new OA\Property(property: 'user_id', type: 'integer', example: 1),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Server error'),
        ]
    )]
    public function store(StoreTaskRequest $request)
    {
        try {

            $data = $this->service->createTask($request->validated());

            return response()->success($data, 'Task Created Successfully');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }
    }

    #[OA\Put(
        path: '/tasks/{id}',
        summary: 'Update a task',
        security: [['bearerAuth' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: 'Task ID'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Updated task title'),
                    new OA\Property(property: 'description', type: 'string', example: 'Updated task description'),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2025-11-15'),
                    new OA\Property(property: 'status', type: 'integer', enum: [1, 2, 3], example: 2, description: '1=Pending, 2=InProgress, 3=Done'),
                    new OA\Property(property: 'priority', type: 'integer', enum: [1, 2, 3], example: 2, description: '1=Low, 2=Medium, 3=High'),
                    new OA\Property(property: 'user_id', type: 'integer', example: 3, description: 'User ID (Admin only - reassigns task)'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Task updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Task Updated Successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'title', type: 'string', example: 'Updated task title'),
                                new OA\Property(property: 'description', type: 'string', example: 'Updated task description'),
                                new OA\Property(property: 'status', type: 'integer', example: 2),
                                new OA\Property(property: 'priority', type: 'integer', example: 2),
                                new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2025-11-15'),
                                new OA\Property(property: 'user_id', type: 'integer', example: 1),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden - Can only update own tasks'),
            new OA\Response(response: 404, description: 'Task not found'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Server error'),
        ]
    )]
    public function update(Task $task, UpdateTaskRequest $request): JsonResponse
    {
        try {

            $data = $this->service->updateTask($task, $request->validated());

            return response()->success($data, 'Task Updated Successfully');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/tasks/{id}',
        summary: 'Delete a task',
        security: [['bearerAuth' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: 'Task ID'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Task deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Task Deleted Successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden - Can only delete own tasks'),
            new OA\Response(response: 404, description: 'Task not found'),
            new OA\Response(response: 500, description: 'Server error'),
        ]
    )]
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
