<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTagRequest;
use App\Http\Requests\Api\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Services\Interfaces\TagServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TagController extends Controller
{
    use ApiResponse;

    protected TagServiceInterface $tagService;

    public function __construct(TagServiceInterface $tagService)
    {
        $this->tagService = $tagService;
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
        $tags = $this->tagService->getAllTags($perPage);

        return $this->successWithPagination(TagResource::collection($tags), 'Tags retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTagRequest $request
     * @return JsonResponse
     */
    public function store(StoreTagRequest $request): JsonResponse
    {
        $data = $request->validated();
        $tag = $this->tagService->createTag($data);

        return $this->success(new TagResource($tag), 'Tag created successfully', 201);
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
            $tag = $this->tagService->getTagById($id);
            return $this->success(new TagResource($tag), 'Tag retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTagRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateTagRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            $tag = $this->tagService->updateTag($id, $data);

            return $this->success(new TagResource($tag), 'Tag updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error("Tag with ID {$id} not found.", 404);
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
            $this->tagService->deleteTag($id);
            return $this->success(null, 'Tag deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error("Tag with ID {$id} not found.", 404);
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
        
        $tags = $this->tagService->searchTags($keyword, $perPage);

        return $this->successWithPagination(TagResource::collection($tags), 'Tags search results retrieved successfully');
    }
}
