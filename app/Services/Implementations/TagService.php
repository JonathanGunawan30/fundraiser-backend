<?php

namespace App\Services\Implementations;

use App\Models\Tag;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\Interfaces\TagServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class TagService implements TagServiceInterface
{
    protected TagRepositoryInterface $tagRepository;

    public function __construct(TagRepositoryInterface $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllTags(int $perPage): LengthAwarePaginator
    {
        return $this->tagRepository->getAllPaginated($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getTagById(int $id): Tag
    {
        $tag = $this->tagRepository->findById($id);

        if (!$tag) {
            Log::warning('Tag lookup failed: Tag not found', ['tag_id' => $id]);
            throw new ModelNotFoundException("Tag with ID {$id} not found.");
        }

        return $tag;
    }

    /**
     * @inheritDoc
     */
    public function searchTags(string $keyword, int $perPage): LengthAwarePaginator
    {
        return $this->tagRepository->search($keyword, $perPage);
    }

    /**
     * @inheritDoc
     */
    public function createTag(array $data): Tag
    {
        $tag = $this->tagRepository->create($data);

        Log::info('Tag created successfully', [
            'tag_id' => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
        ]);

        return $tag;
    }

    /**
     * @inheritDoc
     */
    public function updateTag(int $id, array $data): Tag
    {
        $tag = $this->tagRepository->update($id, $data);

        Log::info('Tag updated successfully', [
            'tag_id' => $id,
            'name' => $tag->name,
            'slug' => $tag->slug,
        ]);

        return $tag;
    }

    /**
     * @inheritDoc
     */
    public function deleteTag(int $id): bool
    {
        Log::info('Tag deleted successfully', ['tag_id' => $id]);

        return $this->tagRepository->delete($id);
    }
}
