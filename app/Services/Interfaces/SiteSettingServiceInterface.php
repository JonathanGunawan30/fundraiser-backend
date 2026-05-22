<?php

namespace App\Services\Interfaces;

use App\Models\SiteSetting;
use Illuminate\Pagination\LengthAwarePaginator;

interface SiteSettingServiceInterface
{
    /**
     * Get all site settings with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllSiteSettings(int $perPage): LengthAwarePaginator;

    /**
     * Get site setting by ID.
     *
     * @param int $id
     * @return SiteSetting
     */
    public function getSiteSettingById(int $id): SiteSetting;

    /**
     * Search site settings.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchSiteSettings(string $keyword, int $perPage): LengthAwarePaginator;

    /**
     * Create a new site setting.
     *
     * @param array $data
     * @return SiteSetting
     */
    public function createSiteSetting(array $data): SiteSetting;

    /**
     * Update an existing site setting.
     *
     * @param int $id
     * @param array $data
     * @return SiteSetting
     */
    public function updateSiteSetting(int $id, array $data): SiteSetting;

    /**
     * Delete a site setting.
     *
     * @param int $id
     * @return bool
     */
    public function deleteSiteSetting(int $id): bool;
}
