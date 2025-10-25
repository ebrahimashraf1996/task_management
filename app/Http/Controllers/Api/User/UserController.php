<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\StoreUserRequest;
use App\Http\Requests\Api\User\UpdateUserRequest;
use App\Http\Requests\Api\User\UserFilterRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    protected UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    #[OA\Get(
        path: '/users',
        summary: 'Get list of users',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'name', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Filter by user name'),
            new OA\Parameter(name: 'email', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'email'), description: 'Filter by email'),
            new OA\Parameter(name: 'role', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['admin', 'user']), description: 'Filter by role'),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc']), description: 'Sort order'),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1), description: 'Items per page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Users list retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Users List'),
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
                                            new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                                            new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                                            new OA\Property(property: 'role', type: 'string', example: 'user'),
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
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 500, description: 'Server error'),
        ]
    )]
    public function index(UserFilterRequest $request): JsonResponse
    {
        try {

            $data = $this->service->getUsers($request->validated());

            return response()->success($data, 'Users List');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }

    }

    #[OA\Post(
        path: '/users',
        summary: 'Create a new user',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'role'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Jane Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, example: 'jane@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'password123'),
                    new OA\Property(property: 'role', type: 'string', enum: ['admin', 'user'], example: 'user'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User Created Successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 2),
                                new OA\Property(property: 'name', type: 'string', example: 'Jane Doe'),
                                new OA\Property(property: 'email', type: 'string', example: 'jane@example.com'),
                                new OA\Property(property: 'role', type: 'string', example: 'user'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden - Admin only'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Server error'),
        ]
    )]
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {

            $data = $this->service->createUser($request->validated());

            return response()->success($data, 'User Created Successfully');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }

    }

    #[OA\Put(
        path: '/users/{id}',
        summary: 'Update a user',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: 'User ID'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Jane Doe Updated'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, example: 'janeupdated@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'newpassword123'),
                    new OA\Property(property: 'role', type: 'string', enum: ['admin', 'user'], example: 'admin'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User Updated Successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 2),
                                new OA\Property(property: 'name', type: 'string', example: 'Jane Doe Updated'),
                                new OA\Property(property: 'email', type: 'string', example: 'janeupdated@example.com'),
                                new OA\Property(property: 'role', type: 'string', example: 'admin'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden - Admin only'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Server error'),
        ]
    )]
    public function update(User $user, UpdateUserRequest $request): JsonResponse
    {
        try {

            $data = $this->service->updateUser($user, $request->validated());

            return response()->success($data, 'User Updated Successfully');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: '/users/{id}',
        summary: 'Delete a user',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: 'User ID'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User Deleted Successfully'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden - Admin only'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 500, description: 'Server error'),
        ]
    )]
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
