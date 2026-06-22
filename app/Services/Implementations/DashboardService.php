<?php

namespace App\Services\Implementations;

use App\Repositories\Interfaces\StatRepositoryInterface;
use App\Services\Interfaces\DashboardServiceInterface;
use App\Models\Donation;
use App\Models\Campaign;

class DashboardService implements DashboardServiceInterface
{
    public function __construct(
        protected StatRepositoryInterface $statRepository
    ) {}

    public function getAdminDashboardData(): array
    {
        $stats = $this->statRepository->getGlobalStats();
        
        return [
            'overview' => $stats,
            'charts' => [
                'donations_last_30_days' => $this->statRepository->getDonationChartData(30),
                'category_distribution' => $this->statRepository->getCategoryDistribution(),
            ],
            'recent_activity' => [
                'donations' => Donation::with(['user', 'campaign'])
                    ->where('status', 'success')
                    ->latest()
                    ->limit(5)
                    ->get(),
                'new_campaigns' => Campaign::with('user')
                    ->where('status', '!=', 'draft')
                    ->latest()
                    ->limit(5)
                    ->get(),
            ]
        ];
    }

    public function getUserDashboardData(int $userId, ?int $days = null): array
    {
        $stats = $this->statRepository->getUserStats($userId, $days);
        
        return [
            'overview' => $stats,
            'charts' => [
                'donations_last_30_days' => $this->statRepository->getDonationChartData(30, $userId),
            ],
            'my_campaigns' => Campaign::where('user_id', $userId)
                ->withCount(['donations' => function($q) {
                    $q->where('status', 'success');
                }])
                ->latest()
                ->limit(5)
                ->get(),
            'recent_donations' => Donation::whereHas('campaign', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->with(['user', 'campaign'])
                ->where('status', 'success')
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }
}
