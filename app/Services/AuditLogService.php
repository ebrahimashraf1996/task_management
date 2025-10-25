<?php

namespace App\Services;

use App\Http\Resources\AuditLog\AuditLogResource;
use App\Http\Resources\PaginatorResource;
use App\Repositories\AuditLogRepository;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class AuditLogService
{
    public function __construct(protected AuditLogRepository $repo) {}

    public function getAuditLogs(array $filters)
    {
        try {

            $auditLogs = $this->repo->getData($filters);

            $response['logs'] = AuditLogResource::collection($auditLogs);

            $response['paginator'] = PaginatorResource::make($auditLogs);

            return $response;

        } catch (\Throwable $e) {

            \Log::error('Some Error Happened : '.$e->getMessage());

            throw new \Exception('Some Error Happened While Getting Audit Logs');
        }
    }

    public function createAuditLog(array $data): void
    {
        \DB::beginTransaction();

        try {

            $this->repo->create($data);

            \DB::commit();

        } catch (\Throwable $e) {

            \DB::rollBack();

            \Log::error('Audit Log Creation Failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new InternalErrorException('Failed To Create Audit Log');
        }
    }
}
