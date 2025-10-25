<?php

namespace App\Services;

use App\Http\Resources\PaginatorResource;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class UserService
{
    public function __construct(protected UserRepository $repo) {}

    public function register(array $data): array
    {
        \DB::beginTransaction();

        try {

            $user = $this->repo->create($data);

            $response['user'] = UserResource::make($user->refresh());

            $response['token'] = $user->getAccessToken();

            \DB::commit();

            return $response;

        } catch (\Throwable $e) {

            \DB::rollBack();

            \Log::error('User Registration Failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new InternalErrorException('Failed To Register User');
        }
    }

    public function login(array $credentials): array
    {
        \DB::beginTransaction();

        try {
            $user = $this->repo->findByEmail($credentials['email']);

            $response['user'] = UserResource::make($user->refresh());

            $response['token'] = $user->getAccessToken();

            \DB::commit();

            return $response;

        } catch (\Throwable $e) {

            \DB::rollBack();

            \Log::error('User Login Failed', [
                'error' => $e->getMessage(),
                'email' => $credentials['email'] ?? null,
            ]);

            throw new \Exception('Failed To Login');
        }
    }

    public function getUsers(array $filters)
    {
        try {

            $users = $this->repo->filter($filters);

            $response['users'] = UserResource::collection($users);

            $response['paginator'] = PaginatorResource::make($users);

            return $response;

        } catch (\Throwable $e) {

            \Log::error('Some Error Happened : '.$e->getMessage());

            throw new \Exception('Some Error Happened While Getting Users');
        }
    }

    public function createUser(array $data): array
    {
        \DB::beginTransaction();

        try {

            $user = $this->repo->create($data);

            $response['user'] = UserResource::make($user->refresh());

            \DB::commit();

            return $response;

        } catch (\Throwable $e) {

            \DB::rollBack();

            \Log::error('User Creation Failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new InternalErrorException('Failed To Create User');
        }
    }

    public function updateUser(User $user, array $data): array
    {
        \DB::beginTransaction();

        try {

            $this->repo->update($user, $data);

            $response['user'] = UserResource::make($user->refresh());

            \DB::commit();

            return $response;

        } catch (\Throwable $e) {

            \DB::rollBack();

            \Log::error('User Update Failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new InternalErrorException('Failed To Update User');
        }
    }

    public function deleteUser(User $user): void
    {
        \DB::beginTransaction();

        try {

            $this->repo->delete($user);

            \DB::commit();

        } catch (\Throwable $e) {

            \DB::rollBack();

            \Log::error('User Delete Failed', [
                'error' => $e->getMessage(),
            ]);

            throw new InternalErrorException('Failed To Delete User');
        }
    }
}
