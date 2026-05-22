<?php

namespace App\Repositories\Implementations;

use App\Models\CampaignImage;
use App\Repositories\Interfaces\CampaignImageRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CampaignImageRepository implements CampaignImageRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getAllPaginated(int $perPage): LengthAwarePaginator
    {
        return CampaignImage::with('campaign')->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?CampaignImage
    {
        return CampaignImage::with('campaign')->find($id);
    }

    /**
     * @inheritDoc
     */
    public function createMany(int $campaignId, array $imagesData): void
    {
        foreach ($imagesData as $data) {
            CampaignImage::create(array_merge(['campaign_id' => $campaignId], $data));
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteMany(array $imageIds): void
    {
        CampaignImage::whereIn('id', $imageIds)->delete();
    }

    /**
     * @inheritDoc
     */
    public function deleteAllForCampaign(int $campaignId): void
    {
        CampaignImage::where('campaign_id', $campaignId)->delete();
    }
}
