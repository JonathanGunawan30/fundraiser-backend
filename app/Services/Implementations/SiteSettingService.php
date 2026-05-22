<?php

namespace App\Services\Implementations;

use App\Models\SiteSetting;
use App\Repositories\Interfaces\SiteSettingRepositoryInterface;
use App\Services\Interfaces\SiteSettingServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SiteSettingService implements SiteSettingServiceInterface
{
    protected SiteSettingRepositoryInterface $siteSettingRepository;

    public function __construct(SiteSettingRepositoryInterface $siteSettingRepository)
    {
        $this->siteSettingRepository = $siteSettingRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllSiteSettings(int $perPage): LengthAwarePaginator
    {
        return $this->siteSettingRepository->getAllPaginated($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getSiteSettingById(int $id): SiteSetting
    {
        $siteSetting = $this->siteSettingRepository->findById($id);

        if (!$siteSetting) {
            throw new ModelNotFoundException("Site Setting with ID {$id} not found.");
        }

        return $siteSetting;
    }

    /**
     * @inheritDoc
     */
    public function searchSiteSettings(string $keyword, int $perPage): LengthAwarePaginator
    {
        return $this->siteSettingRepository->search($keyword, $perPage);
    }

    /**
     * @inheritDoc
     */
    public function createSiteSetting(array $data): SiteSetting
    {
        return $this->siteSettingRepository->create($data);
    }

    /**
     * @inheritDoc
     */
    public function updateSiteSetting(int $id, array $data): SiteSetting
    {
        return $this->siteSettingRepository->update($id, $data);
    }

    /**
     * @inheritDoc
     */
    public function deleteSiteSetting(int $id): bool
    {
        return $this->siteSettingRepository->delete($id);
    }
}
