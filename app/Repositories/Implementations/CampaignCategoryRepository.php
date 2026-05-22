<?php

namespace App\Repositories\Implementations;

use App\Models\CampaignCategory;
use App\Repositories\Interfaces\CampaignCategoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CampaignCategoryRepository implements CampaignCategoryRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getAllPaginated(int $perPage): LengthAwarePaginator
    {
        return CampaignCategory::paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?CampaignCategory
    {
        return CampaignCategory::find($id);
    }

    /**
     * @inheritDoc
     */
    public function search(string $keyword, int $perPage): LengthAwarePaginator
    {
        return CampaignCategory::where('name', 'like', "%{$keyword}%")
            ->orWhere('slug', 'like', "%{$keyword}%")
            ->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): CampaignCategory
    {
        return CampaignCategory::create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): CampaignCategory
    {
        $category = CampaignCategory::findOrFail($id);
        $category->update($data);
        return $category;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $category = CampaignCategory::findOrFail($id);
        return $category->delete();
    }
}
