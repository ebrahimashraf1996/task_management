<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    public function successResponse($data = [], $message = null): JsonResponse
    {
        return response()->json(['code' => 200, 'success' => true, 'message' => $message, 'data' => $data]);
    }

    public function successResponseWithPagination($data = [], $message = null, $pagination = null): JsonResponse
    {
        return response()->json(['code' => 200, 'success' => true, 'message' => $message, 'pagination' => $pagination, 'data' => $data]);
    }

    public function errorResponse($message = null, $code = 200): JsonResponse
    {
        return response()->json(['code' => $code, 'success' => false, 'message' => $message]);
    }

    public function validationErrorsResponse($errors, $message = null, $code = 422): JsonResponse
    {
        return response()->json(['code' => $code, 'success' => false, 'message' => $message, 'data' => $errors]);
    }
}
