<?php

namespace App\Repositories\Implementations;

use App\Models\CampaignUpdate;
use App\Repositories\Interfaces\CampaignUpdateRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CampaignUpdateRepository implements CampaignUpdateRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getAllPaginated(int $perPage): LengthAwarePaginator
    {
        return CampaignUpdate::with(['campaign', 'user'])->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?CampaignUpdate
    {
        return CampaignUpdate::with(['campaign', 'user'])->find($id);
    }

    /**
     * @inheritDoc
     */
    public function search(string $keyword, int $perPage): LengthAwarePaginator
    {
        return CampaignUpdate::with(['campaign', 'user'])
            ->where('title', 'like', "%{$keyword}%")
            ->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): CampaignUpdate
    {
        return CampaignUpdate::create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): CampaignUpdate
    {
        $update = CampaignUpdate::findOrFail($id);
        $update->update($data);
        return $update;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $update = CampaignUpdate::findOrFail($id);
        return $update->delete();
    }
}
