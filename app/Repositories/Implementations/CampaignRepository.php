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
    public function getAllPaginated(int $perPage): LengthAwarePaginator
    {
        return Campaign::with(['user', 'category'])->paginate($perPage);
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
    public function search(string $keyword, int $perPage): LengthAwarePaginator
    {
        return Campaign::with(['user', 'category'])
            ->where('title', 'like', "%{$keyword}%")
            ->orWhere('slug', 'like', "%{$keyword}%")
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
