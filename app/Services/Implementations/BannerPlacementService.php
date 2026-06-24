<?php

namespace App\Services\Implementations;

use App\Models\BannerPlacement;
use App\Repositories\Interfaces\BannerPlacementRepositoryInterface;
use App\Services\Interfaces\BannerPlacementServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class BannerPlacementService implements BannerPlacementServiceInterface
{
    protected BannerPlacementRepositoryInterface $bannerPlacementRepository;

    public function __construct(BannerPlacementRepositoryInterface $bannerPlacementRepository)
    {
        $this->bannerPlacementRepository = $bannerPlacementRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllBannerPlacements(int $perPage): LengthAwarePaginator
    {
        return $this->bannerPlacementRepository->getAllPaginated($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getBannerPlacementById(int $id): BannerPlacement
    {
        $bannerPlacement = $this->bannerPlacementRepository->findById($id);

        if (!$bannerPlacement) {
            Log::warning('Banner placement lookup failed: Placement not found', ['banner_placement_id' => $id]);
            throw new ModelNotFoundException("Banner Placement with ID {$id} not found.");
        }

        return $bannerPlacement;
    }

    /**
     * @inheritDoc
     */
    public function searchBannerPlacements(string $keyword, int $perPage): LengthAwarePaginator
    {
        return $this->bannerPlacementRepository->search($keyword, $perPage);
    }
}
