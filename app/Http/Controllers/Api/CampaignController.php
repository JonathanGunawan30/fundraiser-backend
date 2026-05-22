<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCampaignRequest;
use App\Http\Requests\Api\UpdateCampaignRequest;
use App\Http\Requests\Api\VerifyCampaignRequest;
use App\Http\Resources\CampaignResource;
use App\Services\Interfaces\CampaignServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    use ApiResponse;

    protected CampaignServiceInterface $campaignService;

    public function __construct(CampaignServiceInterface $campaignService)
    {
        $this->campaignService = $campaignService;
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
        $campaigns = $this->campaignService->getAllCampaigns($perPage);

        return $this->successWithPagination(CampaignResource::collection($campaigns), 'Campaigns retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCampaignRequest $request
     * @return JsonResponse
     */
    public function store(StoreCampaignRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image');
        }

        if ($request->hasFile('images')) {
            $data['images'] = $request->file('images');
        }

        $campaign = $this->campaignService->createCampaign($data);

        return $this->success(new CampaignResource($campaign), 'Campaign created successfully', 201);
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
            $campaign = $this->campaignService->getCampaignById($id);
            $campaign->load(['user', 'category', 'tags', 'images', 'updates']);
            return $this->success(new CampaignResource($campaign), 'Campaign retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCampaignRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCampaignRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $request->file('cover_image');
            }

            if ($request->hasFile('images')) {
                $data['images'] = $request->file('images');
            }

            $campaign = $this->campaignService->updateCampaign($id, $data);

            return $this->success(new CampaignResource($campaign), 'Campaign updated successfully');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (ModelNotFoundException $e) {
            return $this->error("Campaign with ID {$id} not found.", 404);
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
            $this->campaignService->deleteCampaign($id);
            return $this->success(null, 'Campaign deleted successfully');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (ModelNotFoundException $e) {
            return $this->error("Campaign with ID {$id} not found.", 404);
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
        
        $campaigns = $this->campaignService->searchCampaigns($keyword, $perPage);

        return $this->successWithPagination(CampaignResource::collection($campaigns), 'Campaigns search results retrieved successfully');
    }

    /**
     * Verify the specified campaign.
     *
     * @param VerifyCampaignRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function verify(VerifyCampaignRequest $request, int $id): JsonResponse
    {
        try {
            $adminId = Auth::guard('admin-api')->id();
            $campaign = $this->campaignService->verifyCampaign($id, $adminId, $request->status);
            
            return $this->success(new CampaignResource($campaign), "Campaign verification status updated to {$request->status}");
        } catch (ModelNotFoundException $e) {
            return $this->error("Campaign with ID {$id} not found.", 404);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
