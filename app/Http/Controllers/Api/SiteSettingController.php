<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSiteSettingRequest;
use App\Http\Requests\Api\UpdateSiteSettingRequest;
use App\Http\Resources\SiteSettingResource;
use App\Services\Interfaces\SiteSettingServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SiteSettingController extends Controller
{
    use ApiResponse;

    protected SiteSettingServiceInterface $siteSettingService;

    public function __construct(SiteSettingServiceInterface $siteSettingService)
    {
        $this->siteSettingService = $siteSettingService;
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
        $siteSettings = $this->siteSettingService->getAllSiteSettings($perPage);

        return $this->successWithPagination(SiteSettingResource::collection($siteSettings), 'Site settings retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSiteSettingRequest $request
     * @return JsonResponse
     */
    public function store(StoreSiteSettingRequest $request): JsonResponse
    {
        $data = $request->validated();
        $siteSetting = $this->siteSettingService->createSiteSetting($data);

        return $this->success(new SiteSettingResource($siteSetting), 'Site setting created successfully', 201);
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
            $siteSetting = $this->siteSettingService->getSiteSettingById($id);
            return $this->success(new SiteSettingResource($siteSetting), 'Site setting retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSiteSettingRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateSiteSettingRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $siteSetting = $this->siteSettingService->updateSiteSetting($id, $data);

            return $this->success(new SiteSettingResource($siteSetting), 'Site setting updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error("Site Setting with ID {$id} not found.", 404);
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
            $this->siteSettingService->deleteSiteSetting($id);
            return $this->success(null, 'Site setting deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error("Site Setting with ID {$id} not found.", 404);
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
        
        $siteSettings = $this->siteSettingService->searchSiteSettings($keyword, $perPage);

        return $this->successWithPagination(SiteSettingResource::collection($siteSettings), 'Site settings search results retrieved successfully');
    }
}
