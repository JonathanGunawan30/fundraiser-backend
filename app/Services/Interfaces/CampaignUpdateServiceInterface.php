<?php

namespace App\Services\Interfaces;

use App\Models\CampaignUpdate;
use Illuminate\Pagination\LengthAwarePaginator;

interface CampaignUpdateServiceInterface
{
    /**
     * Get all campaign updates paginated.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllUpdates(int $perPage): LengthAwarePaginator;

    /**
     * Get campaign update by ID.
     *
     * @param int $id
     * @return CampaignUpdate
     */
    public function getUpdateById(int $id): CampaignUpdate;

    /**
     * Search campaign updates.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchUpdates(string $keyword, int $perPage): LengthAwarePaginator;

    /**
     * Create a new campaign update.
     *
     * @param array $data
     * @return CampaignUpdate
     */
    public function createUpdate(array $data): CampaignUpdate;

    /**
     * Update an existing campaign update.
     *
     * @param int $id
     * @param int $userId
     * @param array $data
     * @return CampaignUpdate
     */
    public function updateUpdate(int $id, int $userId, array $data): CampaignUpdate;

    /**
     * Delete a campaign update.
     *
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function deleteUpdate(int $id, int $userId): bool;
}
