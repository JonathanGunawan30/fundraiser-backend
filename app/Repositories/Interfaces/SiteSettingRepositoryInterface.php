<?php

namespace App\Repositories\Interfaces;

use App\Models\SiteSetting;
use Illuminate\Pagination\LengthAwarePaginator;

interface SiteSettingRepositoryInterface
{
    /**
     * Get all site settings with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage): LengthAwarePaginator;

    /**
     * Get site setting by ID.
     *
     * @param int $id
     * @return SiteSetting|null
     */
    public function findById(int $id): ?SiteSetting;

    /**
     * Search site settings by keyword.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $keyword, int $perPage): LengthAwarePaginator;

    /**
     * Create a new site setting.
     *
     * @param array $data
     * @return SiteSetting
     */
    public function create(array $data): SiteSetting;

    /**
     * Update an existing site setting.
     *
     * @param int $id
     * @param array $data
     * @return SiteSetting
     */
    public function update(int $id, array $data): SiteSetting;

    /**
     * Delete a site setting.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
