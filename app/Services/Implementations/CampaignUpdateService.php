<?php

namespace App\Services\Implementations;

use App\Models\CampaignUpdate;
use App\Repositories\Interfaces\CampaignUpdateRepositoryInterface;
use App\Services\Interfaces\CampaignUpdateServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Models\Campaign;

class CampaignUpdateService implements CampaignUpdateServiceInterface
{
    protected CampaignUpdateRepositoryInterface $updateRepository;

    public function __construct(CampaignUpdateRepositoryInterface $updateRepository)
    {
        $this->updateRepository = $updateRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllUpdates(int $perPage): LengthAwarePaginator
    {
        return $this->updateRepository->getAllPaginated($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getUpdateById(int $id): CampaignUpdate
    {
        $update = $this->updateRepository->findById($id);

        if (!$update) {
            throw new ModelNotFoundException("Campaign update with ID {$id} not found.");
        }

        return $update;
    }

    /**
     * @inheritDoc
     */
    public function searchUpdates(string $keyword, int $perPage): LengthAwarePaginator
    {
        return $this->updateRepository->search($keyword, $perPage);
    }

    /**
     * @inheritDoc
     */
    public function createUpdate(array $data): CampaignUpdate
    {
        // Ownership check is handled in the Request (validation) usually, 
        // but we verify here for safety.
        $campaign = Campaign::findOrFail($data['campaign_id']);
        if ($campaign->user_id !== $data['user_id']) {
            throw new \RuntimeException("You are not authorized to post updates for this campaign.", 403);
        }

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image_url'] = $this->uploadToR2($data['image']);
            unset($data['image']);
        }

        return $this->updateRepository->create($data);
    }

    /**
     * @inheritDoc
     */
    public function updateUpdate(int $id, int $userId, array $data): CampaignUpdate
    {
        $update = $this->getUpdateById($id);

        if ($update->user_id !== $userId) {
            throw new \RuntimeException("You are not authorized to edit this update.", 403);
        }

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($update->image_url) {
                $this->deleteFromR2($update->image_url);
            }
            $data['image_url'] = $this->uploadToR2($data['image']);
            unset($data['image']);
        }

        return $this->updateRepository->update($id, $data);
    }

    /**
     * @inheritDoc
     */
    public function deleteUpdate(int $id, int $userId): bool
    {
        $update = $this->getUpdateById($id);

        if ($update->user_id !== $userId) {
            throw new \RuntimeException("You are not authorized to delete this update.", 403);
        }

        if ($update->image_url) {
            $this->deleteFromR2($update->image_url);
        }

        return $this->updateRepository->delete($id);
    }

    /**
     * Upload file to R2.
     */
    protected function uploadToR2(UploadedFile $file): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('campaigns/updates', $filename, 'r2');
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
