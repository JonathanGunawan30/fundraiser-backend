<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBannerRequest;
use App\Http\Requests\Api\UpdateBannerRequest;
use App\Http\Resources\BannerResource;
use App\Services\Interfaces\BannerServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BannerController extends Controller
{
    use ApiResponse;

    protected BannerServiceInterface $bannerService;

    public function __construct(BannerServiceInterface $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $banners = $this->bannerService->getAllBanners($perPage);

        return $this->successWithPagination(BannerResource::collection($banners), 'Banners retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreBannerRequest $request
     * @return JsonResponse
     */
    public function store(StoreBannerRequest $request): JsonResponse
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }
        
        $banner = $this->bannerService->createBanner($data);

        return $this->success(new BannerResource($banner), 'Banner created successfully', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $banner = $this->bannerService->getBannerById($id);
            $banner->load('placements');
            return $this->success(new BannerResource($banner), 'Banner retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBannerRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateBannerRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image');
            }
            
            $banner = $this->bannerService->updateBanner($id, $data);

            return $this->success(new BannerResource($banner), 'Banner updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error("Banner with ID {$id} not found.", 404);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->bannerService->deleteBanner($id);
            return $this->success(null, 'Banner deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error("Banner with ID {$id} not found.", 404);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Search for resources.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = $request->query('keyword', '');
        $perPage = $request->query('per_page', 10);
        
        $banners = $this->bannerService->searchBanners($keyword, $perPage);

        return $this->successWithPagination(BannerResource::collection($banners), 'Banners search results retrieved successfully');
    }
}
