<?php

namespace App\Repositories\Interfaces;

use App\Models\Tag;
use Illuminate\Pagination\LengthAwarePaginator;

interface TagRepositoryInterface
{
    /**
     * Get all tags with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage): LengthAwarePaginator;

    /**
     * Get tag by ID.
     *
     * @param int $id
     * @return Tag|null
     */
    public function findById(int $id): ?Tag;

    /**
     * Search tags by keyword.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $keyword, int $perPage): LengthAwarePaginator;

    /**
     * Create a new tag.
     *
     * @param array $data
     * @return Tag
     */
    public function create(array $data): Tag;

    /**
     * Update an existing tag.
     *
     * @param int $id
     * @param array $data
     * @return Tag
     */
    public function update(int $id, array $data): Tag;

    /**
     * Delete a tag.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
