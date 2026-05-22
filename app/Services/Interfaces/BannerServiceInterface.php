<?php

namespace App\Services\Interfaces;

use App\Models\Banner;
use Illuminate\Pagination\LengthAwarePaginator;

interface BannerServiceInterface
{
    /**
     * Get all banners with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllBanners(int $perPage): LengthAwarePaginator;

    /**
     * Get banner by ID.
     *
     * @param int $id
     * @return Banner
     */
    public function getBannerById(int $id): Banner;

    /**
     * Search banners.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchBanners(string $keyword, int $perPage): LengthAwarePaginator;

    /**
     * Create a new banner.
     *
     * @param array $data
     * @return Banner
     */
    public function createBanner(array $data): Banner;

    /**
     * Update an existing banner.
     *
     * @param int $id
     * @param array $data
     * @return Banner
     */
    public function updateBanner(int $id, array $data): Banner;

    /**
     * Delete a banner.
     *
     * @param int $id
     * @return bool
     */
    public function deleteBanner(int $id): bool;
}
