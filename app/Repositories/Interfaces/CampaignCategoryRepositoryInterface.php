<?php

namespace App\Repositories\Interfaces;

use App\Models\CampaignCategory;
use Illuminate\Pagination\LengthAwarePaginator;

interface CampaignCategoryRepositoryInterface
{
    /**
     * Get all categories with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage): LengthAwarePaginator;

    /**
     * Get category by ID.
     *
     * @param int $id
     * @return CampaignCategory|null
     */
    public function findById(int $id): ?CampaignCategory;

    /**
     * Search categories by keyword.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $keyword, int $perPage): LengthAwarePaginator;

    /**
     * Create a new category.
     *
     * @param array $data
     * @return CampaignCategory
     */
    public function create(array $data): CampaignCategory;

    /**
     * Update an existing category.
     *
     * @param int $id
     * @param array $data
     * @return CampaignCategory
     */
    public function update(int $id, array $data): CampaignCategory;

    /**
     * Delete a category.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
