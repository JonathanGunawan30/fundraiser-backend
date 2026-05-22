<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCampaignCategoryRequest;
use App\Http\Requests\Api\UpdateCampaignCategoryRequest;
use App\Http\Resources\CampaignCategoryResource;
use App\Services\Interfaces\CampaignCategoryServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CampaignCategoryController extends Controller
{
    use ApiResponse;

    protected CampaignCategoryServiceInterface $categoryService;

    public function __construct(CampaignCategoryServiceInterface $categoryService)
    {
        $this->categoryService = $categoryService;
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
        $categories = $this->categoryService->getAllCategories($perPage);

        return $this->successWithPagination(CampaignCategoryResource::collection($categories), 'Campaign categories retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCampaignCategoryRequest $request
     * @return JsonResponse
     */
    public function store(StoreCampaignCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon');
        }

        $category = $this->categoryService->createCategory($data);

        return $this->success(new CampaignCategoryResource($category), 'Campaign category created successfully', 201);
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
            $category = $this->categoryService->getCategoryById($id);
            return $this->success(new CampaignCategoryResource($category), 'Campaign category retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCampaignCategoryRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCampaignCategoryRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            if ($request->hasFile('icon')) {
                $data['icon'] = $request->file('icon');
            }

            $category = $this->categoryService->updateCategory($id, $data);

            return $this->success(new CampaignCategoryResource($category), 'Campaign category updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error("Campaign category with ID {$id} not found.", 404);
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
            $this->categoryService->deleteCategory($id);
            return $this->success(null, 'Campaign category deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error("Campaign category with ID {$id} not found.", 404);
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
        
        $categories = $this->categoryService->searchCategories($keyword, $perPage);

        return $this->successWithPagination(CampaignCategoryResource::collection($categories), 'Campaign categories search results retrieved successfully');
    }
}
