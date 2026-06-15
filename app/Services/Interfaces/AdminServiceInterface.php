<?php

namespace App\Services\Interfaces;

use App\Models\Admin;
use Illuminate\Pagination\LengthAwarePaginator;

interface AdminServiceInterface
{
    /**
     * Get all admins with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllAdmins(int $perPage): LengthAwarePaginator;

    /**
     * Get admin by ID.
     *
     * @param int $id
     * @return Admin
     */
    public function getAdminById(int $id): Admin;

    /**
     * Search admins.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchAdmins(string $keyword, int $perPage): LengthAwarePaginator;

    /**
     * Update admin profile.
     *
     * @param int $id
     * @param array $data
     * @return Admin
     */
    public function updateProfile(int $id, array $data): Admin;

    /**
     * Update admin password.
     *
     * @param int $id
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword(int $id, string $currentPassword, string $newPassword): bool;
}
