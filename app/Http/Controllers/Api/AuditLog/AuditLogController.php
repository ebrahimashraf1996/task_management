<?php

namespace App\Http\Controllers\Api\AuditLog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AuditLog\AuditLogFilterRequest;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    protected AuditLogService $service;

    public function __construct(AuditLogService $service)
    {
        $this->service = $service;
    }

    public function index(AuditLogFilterRequest $request): JsonResponse
    {
        try {

            $data = $this->service->getAuditLogs($request->validated());

            return response()->success($data, 'AuditLogs List');

        } catch (\Throwable $e) {

            return response()->error([], $e->getMessage(), 500);

        }
    }
}
