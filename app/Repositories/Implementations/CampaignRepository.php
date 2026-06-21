<?php

namespace App\Repositories\Implementations;

use App\Models\Campaign;
use App\Repositories\Interfaces\CampaignRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CampaignRepository implements CampaignRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getAllPaginated(int $perPage, ?string $categorySlug = null): LengthAwarePaginator
    {
        $query = Campaign::with(['user', 'category'])
            ->where('status', 'active');

        if ($categorySlug) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getUserCampaignsPaginated(int $userId, int $perPage): LengthAwarePaginator
    {
        return Campaign::with(['user', 'category'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getAdminCampaignsPaginated(int $perPage, ?string $status = null): LengthAwarePaginator
    {
        $query = Campaign::with(['user', 'category'])->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        } else {
            // By default, admins don't see drafts
            $query->where('status', '!=', 'draft');
        }

        return $query->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function findBySlug(string $slug): ?Campaign
    {
        return Campaign::with(['user', 'category', 'tags', 'images', 'updates', 'verifier'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?Campaign
    {
        return Campaign::with(['user', 'category', 'tags', 'images', 'updates', 'verifier'])->find($id);
    }

    /**
     * @inheritDoc
     */
    public function search(string $keyword, int $perPage, ?string $categorySlug = null): LengthAwarePaginator
    {
        $query = Campaign::with(['user', 'category'])
            ->where('status', 'active')
            ->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%");
            });

        if ($categorySlug) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Campaign
    {
        return Campaign::create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Campaign
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->update($data);
        return $campaign;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $campaign = Campaign::findOrFail($id);
        return $campaign->delete();
    }

    /**
     * @inheritDoc
     */
    public function syncTags(Campaign $campaign, array $tagIds): void
    {
        $campaign->tags()->sync($tagIds);
    }
}
