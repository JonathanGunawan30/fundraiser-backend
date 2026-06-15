<?php

namespace App\Repositories\Interfaces;

use App\Models\Admin;
use Illuminate\Pagination\LengthAwarePaginator;

interface AdminRepositoryInterface
{
    /**
     * Get all admins with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage): LengthAwarePaginator;

    /**
     * Get admin by ID.
     *
     * @param int $id
     * @return Admin|null
     */
    public function findById(int $id): ?Admin;

    /**
     * Search admins by keyword.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $keyword, int $perPage): LengthAwarePaginator;

    /**
     * Update admin.
     *
     * @param Admin $admin
     * @param array $data
     * @return Admin
     */
    public function update(Admin $admin, array $data): Admin;
}
