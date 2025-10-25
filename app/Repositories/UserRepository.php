<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class UserRepository
{
    public function query(): Builder
    {
        return User::query();
    }

    public function find($id): ?User
    {
        return $this->query()->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        return $this->query()

            ->when($filters['name'] ?? null, fn ($query, $value) => $query->where('name', $value))

            ->when($filters['email'] ?? null, fn ($query, $value) => $query->where('email', $value))

            ->when($filters['role'] ?? null, fn ($query, $value) => $query->where('role', $value))

            ->when($filters['sort'] ?? null, fn ($query, $value) => $query->orderBy('created_at', $value))

            ->paginate($filters['per_page'] ?? 10);
    }
}
