<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\DashboardServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected DashboardServiceInterface $dashboardService
    ) {}

    /**
     * Get dashboard data for admin.
     */
    public function adminIndex(): JsonResponse
    {
        $data = $this->dashboardService->getAdminDashboardData();
        return $this->success($data, 'Admin dashboard statistics retrieved successfully');
    }

    /**
     * Get dashboard data for authenticated user.
     */
    public function userIndex(\Illuminate\Http\Request $request): JsonResponse
    {
        $userId = Auth::id();
        $days = $request->query('days');
        $daysVal = ($days === 'all' || !$days) ? null : (int) $days;
        
        $data = $this->dashboardService->getUserDashboardData($userId, $daysVal);
        return $this->success($data, 'User dashboard statistics retrieved successfully');
    }
}
