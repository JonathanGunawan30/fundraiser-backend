<?php

namespace App\Repositories\Implementations;

use App\Models\SiteSetting;
use App\Repositories\Interfaces\SiteSettingRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SiteSettingRepository implements SiteSettingRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getAllPaginated(int $perPage): LengthAwarePaginator
    {
        return SiteSetting::paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?SiteSetting
    {
        return SiteSetting::find($id);
    }

    /**
     * @inheritDoc
     */
    public function search(string $keyword, int $perPage): LengthAwarePaginator
    {
        return SiteSetting::where('key', 'like', "%{$keyword}%")
            ->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): SiteSetting
    {
        return SiteSetting::create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): SiteSetting
    {
        $siteSetting = SiteSetting::findOrFail($id);
        $siteSetting->update($data);
        return $siteSetting;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $siteSetting = SiteSetting::findOrFail($id);
        return $siteSetting->delete();
    }
}
