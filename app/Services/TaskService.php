<?php

namespace App\Services;

use App\Http\Resources\PaginatorResource;
use App\Http\Resources\Task\TaskResource;
use App\Models\Task;
use App\Repositories\TaskRepository;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class TaskService
{
    public function __construct(protected TaskRepository $repo) {}

    public function getTasks(array $filters)
    {
        try {

            $tasks = $this->repo->filter($filters);

            $response['tasks'] = TaskResource::collection($tasks);

            $response['paginator'] = PaginatorResource::make($tasks);

            return $response;

        } catch (\Throwable $e) {

            \Log::error('Some Error Happened : '.$e->getMessage());

            throw new \Exception('Some Error Happened While Getting Tasks');
        }
    }

    public function createTask(array $data): array
    {
        \DB::beginTransaction();

        try {

            if (! Auth::user()->isAdmin()) {

                $data['user_id'] = Auth::id();
            }

            $task = $this->repo->create($data);

            $response['task'] = TaskResource::make($task->refresh());

            \DB::commit();

            return $response;

        } catch (\Throwable $e) {

            \DB::rollBack();

            \Log::error('Task Creation Failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new InternalErrorException('Failed To Create Task');
        }
    }

    public function updateTask(Task $task, array $data): array
    {
        \DB::beginTransaction();

        try {

            $this->repo->update($task, $data);

            $response['task'] = TaskResource::make($task->refresh());

            \DB::commit();

            return $response;

        } catch (\Throwable $e) {

            \DB::rollBack();

            \Log::error('Task Update Failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new InternalErrorException('Failed To Update Task');
        }
    }

    public function deleteTask(Task $task): void
    {
        \DB::beginTransaction();

        try {

            $this->repo->delete($task);

            \DB::commit();

        } catch (\Throwable $e) {

            \DB::rollBack();

            \Log::error('Task Delete Failed', [
                'error' => $e->getMessage(),
            ]);

            throw new InternalErrorException('Failed To Delete Task');
        }
    }
}
