<?php

namespace App\Repositories\Implementations;

use Spatie\Activitylog\Models\Activity;
use App\Repositories\Interfaces\AuditLogRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditLogRepository implements AuditLogRepositoryInterface
{
    public function getAllPaginated(int $perPage): LengthAwarePaginator
    {
        return Activity::with(['causer', 'subject'])->latest()->paginate($perPage);
    }

    public function findById(int $id): ?object
    {
        return Activity::with(['causer', 'subject'])->find($id);
    }
}
