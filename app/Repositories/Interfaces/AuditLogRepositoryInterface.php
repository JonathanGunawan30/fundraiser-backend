<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface AuditLogRepositoryInterface
{
    /**
     * Get all activity logs paginated.
     */
    public function getAllPaginated(int $perPage): LengthAwarePaginator;

    /**
     * Find log by ID.
     */
    public function findById(int $id): ?object;
}
