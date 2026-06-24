<?php

namespace App\Services\Implementations;

use App\Models\SiteSetting;
use App\Repositories\Interfaces\SiteSettingRepositoryInterface;
use App\Services\Interfaces\SiteSettingServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

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
        $siteSetting = $this->siteSettingRepository->create($data);
        Log::info('Site setting created', ['key' => $siteSetting->key, 'value' => $siteSetting->value]);
        return $siteSetting;
    }

    /**
     * @inheritDoc
     */
    public function updateSiteSetting(int $id, array $data): SiteSetting
    {
        $siteSetting = $this->siteSettingRepository->update($id, $data);
        Log::info('Site setting updated', ['setting_id' => $id, 'key' => $siteSetting->key, 'value' => $siteSetting->value]);
        return $siteSetting;
    }

    /**
     * @inheritDoc
     */
    public function deleteSiteSetting(int $id): bool
    {
        Log::info('Site setting deleted', ['setting_id' => $id]);
        return $this->siteSettingRepository->delete($id);
    }
}
