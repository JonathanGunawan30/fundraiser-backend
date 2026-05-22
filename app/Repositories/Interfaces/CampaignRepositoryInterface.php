<?php

namespace App\Repositories\Interfaces;

use App\Models\Campaign;
use Illuminate\Pagination\LengthAwarePaginator;

interface CampaignRepositoryInterface
{
    /**
     * Get all campaigns paginated.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage): LengthAwarePaginator;

    /**
     * Find campaign by ID.
     *
     * @param int $id
     * @return Campaign|null
     */
    public function findById(int $id): ?Campaign;

    /**
     * Search campaigns.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $keyword, int $perPage): LengthAwarePaginator;

    /**
     * Create a new campaign.
     *
     * @param array $data
     * @return Campaign
     */
    public function create(array $data): Campaign;

    /**
     * Update an existing campaign.
     *
     * @param int $id
     * @param array $data
     * @return Campaign
     */
    public function update(int $id, array $data): Campaign;

    /**
     * Delete a campaign.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Sync tags for a campaign.
     *
     * @param Campaign $campaign
     * @param array $tagIds
     * @return void
     */
    public function syncTags(Campaign $campaign, array $tagIds): void;
}
