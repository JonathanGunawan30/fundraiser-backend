<?php

namespace App\Services\Implementations;

use App\Models\CampaignImage;
use App\Repositories\Interfaces\CampaignImageRepositoryInterface;
use App\Services\Interfaces\CampaignImageServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class CampaignImageService implements CampaignImageServiceInterface
{
    protected CampaignImageRepositoryInterface $imageRepository;

    public function __construct(CampaignImageRepositoryInterface $imageRepository)
    {
        $this->imageRepository = $imageRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllImages(int $perPage): LengthAwarePaginator
    {
        return $this->imageRepository->getAllPaginated($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getImageById(int $id): CampaignImage
    {
        $image = $this->imageRepository->findById($id);

        if (!$image) {
            Log::warning('Campaign image lookup failed: Image not found', ['image_id' => $id]);
            throw new ModelNotFoundException("Campaign image with ID {$id} not found.");
        }

        return $image;
    }

    /**
     * @inheritDoc
     */
    public function searchImages(string $keyword, int $perPage): LengthAwarePaginator
    {
        return $this->imageRepository->search($keyword, $perPage);
    }
}
