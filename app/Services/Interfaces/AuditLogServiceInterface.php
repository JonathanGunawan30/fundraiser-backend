<?php

namespace App\Services\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface AuditLogServiceInterface
{
    public function getAllLogs(int $perPage): LengthAwarePaginator;
    public function getLogById(int $id): object;
}
