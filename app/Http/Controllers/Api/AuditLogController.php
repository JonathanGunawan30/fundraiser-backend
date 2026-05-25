<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Services\Interfaces\AuditLogServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuditLogServiceInterface $logService
    ) {}

    /**
     * List all audit logs for admin.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 20);
        $logs = $this->logService->getAllLogs($perPage);

        return $this->successWithPagination(AuditLogResource::collection($logs), 'Audit logs retrieved successfully');
    }

    /**
     * Show single log detail.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $log = $this->logService->getLogById($id);
            return $this->success(new AuditLogResource($log), 'Audit log details retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 404);
        }
    }
}
