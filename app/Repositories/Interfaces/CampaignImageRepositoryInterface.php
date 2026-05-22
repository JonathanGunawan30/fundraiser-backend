<?php

namespace App\Repositories\Interfaces;

use App\Models\CampaignImage;
use Illuminate\Pagination\LengthAwarePaginator;

interface CampaignImageRepositoryInterface
{
    /**
     * Get all campaign images paginated.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage): LengthAwarePaginator;

    /**
     * Find campaign image by ID.
     *
     * @param int $id
     * @return CampaignImage|null
     */
    public function findById(int $id): ?CampaignImage;

    /**
     * Create multiple images for a campaign.
     *
     * @param int $campaignId
     * @param array $imagesData
     * @return void
     */
    public function createMany(int $campaignId, array $imagesData): void;

    /**
     * Delete multiple images for a campaign.
     *
     * @param array $imageIds
     * @return void
     */
    public function deleteMany(array $imageIds): void;

    /**
     * Delete all images for a campaign.
     *
     * @param int $campaignId
     * @return void
     */
    public function deleteAllForCampaign(int $campaignId): void;
}
