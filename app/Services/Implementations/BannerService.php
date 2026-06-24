<?php

namespace App\Services\Implementations;

use App\Models\Banner;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use App\Services\Interfaces\BannerServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class BannerService implements BannerServiceInterface
{
    protected BannerRepositoryInterface $bannerRepository;

    public function __construct(BannerRepositoryInterface $bannerRepository)
    {
        $this->bannerRepository = $bannerRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllBanners(int $perPage): LengthAwarePaginator
    {
        return $this->bannerRepository->getAllPaginated($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getBannerById(int $id): Banner
    {
        $banner = $this->bannerRepository->findById($id);

        if (!$banner) {
            Log::warning('Banner lookup failed: Banner not found', ['banner_id' => $id]);
            throw new ModelNotFoundException("Banner with ID {$id} not found.");
        }

        return $banner;
    }

    /**
     * @inheritDoc
     */
    public function searchBanners(string $keyword, int $perPage): LengthAwarePaginator
    {
        return $this->bannerRepository->search($keyword, $perPage);
    }

    /**
     * @inheritDoc
     */
    public function createBanner(array $data): Banner
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $filename = Str::uuid() . '.' . $data['image']->getClientOriginalExtension();
            $path = $data['image']->storeAs('banners', $filename, 'r2');
            $data['image_url'] = Storage::disk('r2')->url($path);
            unset($data['image']);
        }

        $banner = $this->bannerRepository->create($data);

        Log::info('Banner created successfully', [
            'banner_id' => $banner->id,
            'title' => $banner->title,
            'placement_id' => $banner->banner_placement_id,
        ]);

        return $banner;
    }

    /**
     * @inheritDoc
     */
    public function updateBanner(int $id, array $data): Banner
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $banner = $this->getBannerById($id);
            if ($banner->image_url) {
                $this->deleteFileFromR2($banner->image_url);
            }

            $filename = Str::uuid() . '.' . $data['image']->getClientOriginalExtension();
            $path = $data['image']->storeAs('banners', $filename, 'r2');
            $data['image_url'] = Storage::disk('r2')->url($path);
            unset($data['image']);
        }

        $updatedBanner = $this->bannerRepository->update($id, $data);

        Log::info('Banner updated successfully', [
            'banner_id' => $id,
            'title' => $updatedBanner->title,
            'placement_id' => $updatedBanner->banner_placement_id,
        ]);

        return $updatedBanner;
    }

    /**
     * @inheritDoc
     */
    public function deleteBanner(int $id): bool
    {
        $banner = $this->getBannerById($id);
        if ($banner->image_url) {
            $this->deleteFileFromR2($banner->image_url);
        }

        Log::info('Banner deleted successfully', ['banner_id' => $id, 'title' => $banner->title]);

        return $this->bannerRepository->delete($id);
    }

    /**
     * Delete file from R2 by URL.
     *
     * @param string $url
     * @return void
     */
    protected function deleteFileFromR2(string $url): void
    {
        $baseUrl = Storage::disk('r2')->url('');
        $path = str_replace($baseUrl, '', $url);
        $path = ltrim($path, '/');
        
        if ($path) {
            Storage::disk('r2')->delete($path);
        }
    }
}
