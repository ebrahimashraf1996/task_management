<?php

namespace App\Http\Controllers\Api\AuditLog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AuditLog\AuditLogFilterRequest;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class AuditLogController extends Controller
{
    protected AuditLogService $service;

    public function __construct(AuditLogService $service)
    {
        $this->service = $service;
    }

    #[OA\Get(
        path: '/audit-logs',
        summary: 'Get list of audit logs',
        description: 'Retrieve audit trail of task changes and user actions',
        security: [['bearerAuth' => []]],
        tags: ['Audit Logs'],
        parameters: [
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc']), description: 'Sort order'),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1), description: 'Items per page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Audit logs retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'AuditLogs List'),
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
                                            new OA\Property(property: 'user_id', type: 'integer', example: 1),
                                            new OA\Property(property: 'task_id', type: 'integer', example: 5),
                                            new OA\Property(property: 'action', type: 'string', example: 'updated'),
                                            new OA\Property(property: 'old_values', type: 'object', example: ['status' => 1]),
                                            new OA\Property(property: 'new_values', type: 'object', example: ['status' => 2]),
                                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-10-25T10:30:00Z'),
                                        ]
                                    )
                                ),
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 100),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 500, description: 'Server error'),
        ]
    )]
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
