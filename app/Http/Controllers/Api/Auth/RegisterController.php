<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    protected UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        try {

            $data = $this->service->register($request->validated());

            return response()->success($data, 'User Registered Successfully');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }
    }
}
