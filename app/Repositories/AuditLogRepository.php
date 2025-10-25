<?php

namespace App\Repositories;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AuditLogRepository
{
    public function query(): Builder
    {
        return AuditLog::query();
    }

    public function create(array $data): AuditLog
    {
        return AuditLog::create($data);
    }

    public function getData(array $filters): LengthAwarePaginator
    {
        return $this->query()

            ->when($filters['sort'] ?? null, fn ($query, $value) => $query->orderBy('created_at', $value))

            ->paginate($filters['per_page'] ?? 10);
    }
}
