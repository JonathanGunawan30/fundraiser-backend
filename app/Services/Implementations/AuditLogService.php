<?php

namespace App\Services\Implementations;

use App\Repositories\Interfaces\AuditLogRepositoryInterface;
use App\Services\Interfaces\AuditLogServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class AuditLogService implements AuditLogServiceInterface
{
    public function __construct(
        protected AuditLogRepositoryInterface $logRepository
    ) {}

    public function getAllLogs(int $perPage): LengthAwarePaginator
    {
        return $this->logRepository->getAllPaginated($perPage);
    }

    public function getLogById(int $id): object
    {
        $log = $this->logRepository->findById($id);
        if (!$log) {
            Log::warning('Audit log lookup failed: Log not found', ['log_id' => $id]);
            throw new ModelNotFoundException("Log with ID {$id} not found.");
        }
        return $log;
    }
}
