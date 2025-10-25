<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\StoreUserRequest;
use App\Http\Requests\Api\User\UpdateUserRequest;
use App\Http\Requests\Api\User\UserFilterRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function index(UserFilterRequest $request): JsonResponse
    {
        try {

            $data = $this->service->getUsers($request->validated());

            return response()->success($data, 'Users List');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }

    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {

            $data = $this->service->createUser($request->validated());

            return response()->success($data, 'User Created Successfully');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }

    }

    public function update(User $user, UpdateUserRequest $request): JsonResponse
    {
        try {

            $data = $this->service->updateUser($user, $request->validated());

            return response()->success($data, 'User Updated Successfully');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }
    }

    public function destroy(User $user): JsonResponse
    {
        try {
            if (auth()->user()->cannot('deleteAny', User::class)) {
                return response()->error([], 'You are not authorized to perform this action.', 403);
            }

            $this->service->deleteUser($user);

            return response()->success([], 'User Deleted Successfully');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }
    }
}
