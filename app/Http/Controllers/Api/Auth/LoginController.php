<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    protected UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function __invoke(LoginRequest $request): JsonResponse
    {
        try {

            $data = $this->service->login($request->validated());

            return response()->success($data, 'User Logged In Successfully');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);
        }
    }
}
