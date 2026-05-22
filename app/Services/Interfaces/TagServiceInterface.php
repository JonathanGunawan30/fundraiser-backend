<?php

namespace App\Services\Interfaces;

use App\Models\Tag;
use Illuminate\Pagination\LengthAwarePaginator;

interface TagServiceInterface
{
    /**
     * Get all tags with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllTags(int $perPage): LengthAwarePaginator;

    /**
     * Get tag by ID.
     *
     * @param int $id
     * @return Tag
     */
    public function getTagById(int $id): Tag;

    /**
     * Search tags.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchTags(string $keyword, int $perPage): LengthAwarePaginator;

    /**
     * Create a new tag.
     *
     * @param array $data
     * @return Tag
     */
    public function createTag(array $data): Tag;

    /**
     * Update an existing tag.
     *
     * @param int $id
     * @param array $data
     * @return Tag
     */
    public function updateTag(int $id, array $data): Tag;

    /**
     * Delete a tag.
     *
     * @param int $id
     * @return bool
     */
    public function deleteTag(int $id): bool;
}
