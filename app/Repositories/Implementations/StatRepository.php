<?php

namespace App\Repositories\Implementations;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use App\Models\CampaignCategory;
use App\Repositories\Interfaces\StatRepositoryInterface;
use Illuminate\Support\Facades\DB;

class StatRepository implements StatRepositoryInterface
{
    public function getGlobalStats(): array
    {
        return [
            'total_donations_amount' => (int) Donation::where('status', 'success')->sum('amount'),
            'total_donations_count' => Donation::where('status', 'success')->count(),
            'total_campaigns_count' => Campaign::count(),
            'total_campaigns_active' => Campaign::where('status', 'active')->count(),
            'total_users_count' => User::count(),
        ];
    }

    public function getUserStats(int $userId): array
    {
        $campaignIds = Campaign::where('user_id', $userId)->pluck('id');

        return [
            'total_raised_amount' => (int) Donation::whereIn('campaign_id', $campaignIds)
                ->where('status', 'success')
                ->sum('amount'),
            'total_donors_count' => Donation::whereIn('campaign_id', $campaignIds)
                ->where('status', 'success')
                ->count(),
            'active_campaigns_count' => Campaign::where('user_id', $userId)
                ->where('status', 'active')
                ->count(),
        ];
    }

    public function getDonationChartData(int $days, ?int $userId = null): array
    {
        $query = Donation::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('status', 'success')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date', 'ASC');

        if ($userId) {
            $campaignIds = Campaign::where('user_id', $userId)->pluck('id');
            $query->whereIn('campaign_id', $campaignIds);
        }

        return $query->get()->toArray();
    }

    public function getCategoryDistribution(): array
    {
        return CampaignCategory::select('name')
            ->withCount('campaigns')
            ->get()
            ->map(function($item) {
                return [
                    'label' => $item->name,
                    'value' => $item->campaigns_count
                ];
            })
            ->toArray();
    }
}
