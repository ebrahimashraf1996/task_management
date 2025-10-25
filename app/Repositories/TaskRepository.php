<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TaskRepository
{
    public function query(): Builder
    {
        return Task::query()
            ->when(! Auth::user()->isAdmin(), fn ($q) => $q->where('user_id', Auth::id()));
    }

    public function find($id): ?Task
    {
        return $this->query()->find($id);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): bool
    {
        return $task->update($data);
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        return $this->query()

            ->when($filters['priority'] ?? null, fn ($query, $value) => $query->where('priority', $value))

            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))

            ->when(($filters['due_from'] ?? null && $filters['due_to'] ?? null),
                fn ($query) => $query->whereBetween('due_date', [$filters['due_from'], $filters['due_to']]))

            ->when($filters['sort'] ?? null, fn ($query, $value) => $query->orderBy('created_at', $value))

            ->paginate($filters['per_page'] ?? 10);
    }
}
