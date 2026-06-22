<?php

namespace App\Services\Interfaces;

interface DashboardServiceInterface
{
    /**
     * Get full admin dashboard data.
     */
    public function getAdminDashboardData(): array;

    /**
     * Get full user dashboard data.
     */
    public function getUserDashboardData(int $userId, ?int $days = null): array;
}
