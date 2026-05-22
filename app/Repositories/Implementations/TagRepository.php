<?php

namespace App\Repositories\Implementations;

use App\Models\Tag;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TagRepository implements TagRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getAllPaginated(int $perPage): LengthAwarePaginator
    {
        return Tag::paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?Tag
    {
        return Tag::find($id);
    }

    /**
     * @inheritDoc
     */
    public function search(string $keyword, int $perPage): LengthAwarePaginator
    {
        return Tag::where('name', 'like', "%{$keyword}%")
            ->orWhere('slug', 'like', "%{$keyword}%")
            ->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Tag
    {
        return Tag::create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Tag
    {
        $tag = Tag::findOrFail($id);
        $tag->update($data);
        return $tag;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $tag = Tag::findOrFail($id);
        return $tag->delete();
    }
}
