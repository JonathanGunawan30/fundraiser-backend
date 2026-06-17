<?php

namespace App\Services\Interfaces;

use App\Models\Campaign;
use Illuminate\Pagination\LengthAwarePaginator;

interface CampaignServiceInterface
{
    /**
     * Get all campaigns paginated.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllCampaigns(int $perPage): LengthAwarePaginator;

    /**
     * Get user campaigns paginated.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserCampaigns(int $userId, int $perPage): LengthAwarePaginator;

    /**
     * Get admin campaigns paginated.
     *
     * @param int $perPage
     * @param string|null $status
     * @return LengthAwarePaginator
     */
    public function getAdminCampaigns(int $perPage, ?string $status = null): LengthAwarePaginator;

    /**
     * Get campaign by Slug.
     *
     * @param string $slug
     * @return Campaign
     */
    public function getCampaignBySlug(string $slug): Campaign;

    /**
     * Get campaign by ID.
     *
     * @param int $id
     * @return Campaign
     */
    public function getCampaignById(int $id): Campaign;

    /**
     * Search campaigns.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchCampaigns(string $keyword, int $perPage): LengthAwarePaginator;

    /**
     * Create a new campaign.
     *
     * @param array $data
     * @return Campaign
     */
    public function createCampaign(array $data): Campaign;

    /**
     * Update an existing campaign.
     *
     * @param int $id
     * @param array $data
     * @return Campaign
     */
    public function updateCampaign(int $id, array $data): Campaign;

    /**
     * Delete a campaign.
     *
     * @param int $id
     * @return bool
     */
    public function deleteCampaign(int $id): bool;

    /**
     * Verify a campaign.
     *
     * @param int $id
     * @param int $adminId
     * @param string $status
     * @return Campaign
     */
    public function verifyCampaign(int $id, int $adminId, string $status): Campaign;
}
