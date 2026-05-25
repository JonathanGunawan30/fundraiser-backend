<?php

namespace App\Repositories\Interfaces;

interface StatRepositoryInterface
{
    /**
     * Get basic counts and sums for admin.
     */
    public function getGlobalStats(): array;

    /**
     * Get donation stats for a user (as creator).
     */
    public function getUserStats(int $userId): array;

    /**
     * Get donation chart data (daily sums) for a period.
     */
    public function getDonationChartData(int $days, ?int $userId = null): array;

    /**
     * Get campaign distribution by category.
     */
    public function getCategoryDistribution(): array;
}
