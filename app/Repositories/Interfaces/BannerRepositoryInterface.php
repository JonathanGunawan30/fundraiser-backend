<?php

namespace App\Repositories\Interfaces;

use App\Models\Banner;
use Illuminate\Pagination\LengthAwarePaginator;

interface BannerRepositoryInterface
{
    /**
     * Get all banners with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage): LengthAwarePaginator;

    /**
     * Get banner by ID.
     *
     * @param int $id
     * @return Banner|null
     */
    public function findById(int $id): ?Banner;

    /**
     * Search banners.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $keyword, int $perPage): LengthAwarePaginator;

    /**
     * Create a new banner.
     *
     * @param array $data
     * @return Banner
     */
    public function create(array $data): Banner;

    /**
     * Update an existing banner.
     *
     * @param int $id
     * @param array $data
     * @return Banner
     */
    public function update(int $id, array $data): Banner;

    /**
     * Delete a banner.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
    }
