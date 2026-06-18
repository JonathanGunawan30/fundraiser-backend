<?php

namespace App\Services\Implementations;

use App\Models\Campaign;
use App\Repositories\Interfaces\CampaignRepositoryInterface;
use App\Repositories\Interfaces\CampaignImageRepositoryInterface;
use App\Services\Interfaces\CampaignServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendCampaignStatusJob;

class CampaignService implements CampaignServiceInterface
{
    public function __construct(
        protected CampaignRepositoryInterface $campaignRepository,
        protected CampaignImageRepositoryInterface $campaignImageRepository
    ) {}

    /**
     * @inheritDoc
     */
    public function getAllCampaigns(int $perPage): LengthAwarePaginator
    {
        return $this->campaignRepository->getAllPaginated($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getUserCampaigns(int $userId, int $perPage): LengthAwarePaginator
    {
        return $this->campaignRepository->getUserCampaignsPaginated($userId, $perPage);
    }

    /**
     * @inheritDoc
     */
    public function getAdminCampaigns(int $perPage, ?string $status = null): LengthAwarePaginator
    {
        return $this->campaignRepository->getAdminCampaignsPaginated($perPage, $status);
    }

    /**
     * @inheritDoc
     */
    public function getCampaignBySlug(string $slug): Campaign
    {
        $campaign = $this->campaignRepository->findBySlug($slug);

        if (!$campaign) {
            throw new ModelNotFoundException("Campaign with slug '{$slug}' not found.");
        }

        return $campaign;
    }

    /**
     * @inheritDoc
     */
    public function getCampaignById(int $id): Campaign
    {
        $campaign = $this->campaignRepository->findById($id);

        if (!$campaign) {
            throw new ModelNotFoundException("Campaign with ID {$id} not found.");
        }

        return $campaign;
    }

    /**
     * @inheritDoc
     */
    public function searchCampaigns(string $keyword, int $perPage): LengthAwarePaginator
    {
        return $this->campaignRepository->search($keyword, $perPage);
    }

    /**
     * @inheritDoc
     */
    public function createCampaign(array $data): Campaign
    {
        return DB::transaction(function () use ($data) {
            // Force status to draft if not provided or if not admin
            if (!isset($data['status'])) {
                $data['status'] = 'draft';
            }

            // Handle cover image
            if (isset($data['cover_image']) && $data['cover_image'] instanceof UploadedFile) {
                $data['cover_image_url'] = $this->uploadToR2($data['cover_image'], 'campaigns/covers');
                unset($data['cover_image']);
            }

            // Create campaign
            $campaign = $this->campaignRepository->create($data);

            // Handle tags
            if (isset($data['tags'])) {
                $this->campaignRepository->syncTags($campaign, $data['tags']);
            }

            // Handle gallery images
            if (isset($data['images'])) {
                $imagesData = [];
                foreach ($data['images'] as $index => $file) {
                    if ($file instanceof UploadedFile) {
                        $imagesData[] = [
                            'image_url' => $this->uploadToR2($file, 'campaigns/gallery'),
                            'order_index' => $index
                        ];
                    }
                }
                $this->campaignImageRepository->createMany($campaign->id, $imagesData);
            }

            return $campaign;
        });
    }

    /**
     * @inheritDoc
     */
    public function updateCampaign(int $id, array $data): Campaign
    {
        return DB::transaction(function () use ($id, $data) {
            $campaign = $this->getCampaignById($id);

            // Security Rule: Cannot revert to draft if already published (pending/active/completed)
            if (isset($data['status']) && $data['status'] === 'draft' && $campaign->status !== 'draft') {
                throw new \InvalidArgumentException("Cannot revert a published campaign to draft status.");
            }

            // Business Rule: Cannot lower goal amount below collected amount
            if (isset($data['goal_amount']) && $data['goal_amount'] < $campaign->collected_amount) {
                throw new \InvalidArgumentException("Goal amount cannot be lower than currently collected amount (" . number_format($campaign->collected_amount) . ")");
            }

            // Handle cover image
            if (isset($data['cover_image']) && $data['cover_image'] instanceof UploadedFile) {
                if ($campaign->cover_image_url) {
                    $this->deleteFromR2($campaign->cover_image_url);
                }
                $data['cover_image_url'] = $this->uploadToR2($data['cover_image'], 'campaigns/covers');
                unset($data['cover_image']);
            }

            // Update campaign
            $campaign = $this->campaignRepository->update($id, $data);

            // Handle tags
            if (isset($data['tags'])) {
                $this->campaignRepository->syncTags($campaign, $data['tags']);
            }

            // Handle gallery images (replace strategy)
            if (isset($data['images'])) {
                // Delete old images
                foreach ($campaign->images as $image) {
                    $this->deleteFromR2($image->image_url);
                }
                $this->campaignImageRepository->deleteAllForCampaign($campaign->id);

                // Upload new images
                $imagesData = [];
                foreach ($data['images'] as $index => $file) {
                    if ($file instanceof UploadedFile) {
                        $imagesData[] = [
                            'image_url' => $this->uploadToR2($file, 'campaigns/gallery'),
                            'order_index' => $index
                        ];
                    }
                }
                $this->campaignImageRepository->createMany($campaign->id, $imagesData);
            }

            return $campaign;
        });
    }

    /**
     * @inheritDoc
     */
    public function deleteCampaign(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $campaign = $this->getCampaignById($id);

            // Business Rule: Cannot delete campaign if it has already collected donations
            if ($campaign->collected_amount > 0) {
                throw new \RuntimeException("Cannot delete campaign that has already received donations. Consider suspending it instead.");
            }

            // Delete cover
            if ($campaign->cover_image_url) {
                $this->deleteFromR2($campaign->cover_image_url);
            }

            // Delete gallery
            foreach ($campaign->images as $image) {
                $this->deleteFromR2($image->image_url);
            }

            return $this->campaignRepository->delete($id);
        });
    }

    /**
     * @inheritDoc
     */
    public function verifyCampaign(int $id, int $adminId, string $status): Campaign
    {
        $campaign = $this->campaignRepository->update($id, [
            'verified_status' => $status,
            'verified_by' => $adminId,
            'verified_at' => now(),
            'status' => $status === 'approved' ? 'active' : 'suspended'
        ]);

        if ($campaign->user) {
            $campaign->user->notify(new \App\Notifications\CampaignVerifiedNotification($campaign));

            SendCampaignStatusJob::dispatch(
                $campaign->user->email,
                $campaign->user->name,
                $campaign->title,
                $status
            );
        }

        return $campaign;
    }

    /**
     * Upload file to R2.
     */
    protected function uploadToR2(UploadedFile $file, string $folder): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, 'r2');
        return Storage::disk('r2')->url($path);
    }

    /**
     * Delete file from R2.
     */
    protected function deleteFromR2(string $url): void
    {
        $baseUrl = Storage::disk('r2')->url('');
        $path = ltrim(str_replace($baseUrl, '', $url), '/');
        if ($path) {
            Storage::disk('r2')->delete($path);
        }
    }
}
