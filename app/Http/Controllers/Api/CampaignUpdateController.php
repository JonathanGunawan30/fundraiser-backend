<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCampaignUpdateRequest;
use App\Http\Requests\Api\UpdateCampaignUpdateRequest;
use App\Http\Resources\CampaignUpdateResource;
use App\Services\Interfaces\CampaignUpdateServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class CampaignUpdateController extends Controller
{
    use ApiResponse;

    protected CampaignUpdateServiceInterface $updateService;

    public function __construct(CampaignUpdateServiceInterface $updateService)
    {
        $this->updateService = $updateService;
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
        $updates = $this->updateService->getAllUpdates($perPage);

        return $this->successWithPagination(CampaignUpdateResource::collection($updates), 'Campaign updates retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCampaignUpdateRequest $request
     * @return JsonResponse
     */
    public function store(StoreCampaignUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        $update = $this->updateService->createUpdate($data);

        return $this->success(new CampaignUpdateResource($update), 'Campaign update created successfully', 201);
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
            $update = $this->updateService->getUpdateById($id);
            return $this->success(new CampaignUpdateResource($update), 'Campaign update retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCampaignUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCampaignUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image');
            }

            $update = $this->updateService->updateUpdate($id, Auth::id(), $data);

            return $this->success(new CampaignUpdateResource($update), 'Campaign update updated successfully');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 403);
        } catch (ModelNotFoundException $e) {
            return $this->error("Campaign update with ID {$id} not found.", 404);
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
            $this->updateService->deleteUpdate($id, Auth::id());
            return $this->success(null, 'Campaign update deleted successfully');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 403);
        } catch (ModelNotFoundException $e) {
            return $this->error("Campaign update with ID {$id} not found.", 404);
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
        
        $updates = $this->updateService->searchUpdates($keyword, $perPage);

        return $this->successWithPagination(CampaignUpdateResource::collection($updates), 'Campaign updates search results retrieved successfully');
    }
}
