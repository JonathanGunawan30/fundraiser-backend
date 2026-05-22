<?php

namespace App\Services\Implementations;

use App\Models\CampaignCategory;
use App\Repositories\Interfaces\CampaignCategoryRepositoryInterface;
use App\Services\Interfaces\CampaignCategoryServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CampaignCategoryService implements CampaignCategoryServiceInterface
{
    protected CampaignCategoryRepositoryInterface $categoryRepository;

    public function __construct(CampaignCategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllCategories(int $perPage): LengthAwarePaginator
    {
        return $this->categoryRepository->getAllPaginated($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getCategoryById(int $id): CampaignCategory
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new ModelNotFoundException("Campaign category with ID {$id} not found.");
        }

        return $category;
    }

    /**
     * @inheritDoc
     */
    public function searchCategories(string $keyword, int $perPage): LengthAwarePaginator
    {
        return $this->categoryRepository->search($keyword, $perPage);
    }

    /**
     * @inheritDoc
     */
    public function createCategory(array $data): CampaignCategory
    {
        if (isset($data['icon']) && $data['icon'] instanceof UploadedFile) {
            $filename = Str::uuid() . '.' . $data['icon']->getClientOriginalExtension();
            $path = $data['icon']->storeAs('categories', $filename, 'r2');
            $data['icon_url'] = Storage::disk('r2')->url($path);
            unset($data['icon']);
        }

        return $this->categoryRepository->create($data);
    }

    /**
     * @inheritDoc
     */
    public function updateCategory(int $id, array $data): CampaignCategory
    {
        if (isset($data['icon']) && $data['icon'] instanceof UploadedFile) {
            $category = $this->getCategoryById($id);
            if ($category->icon_url) {
                $this->deleteFileFromR2($category->icon_url);
            }

            $filename = Str::uuid() . '.' . $data['icon']->getClientOriginalExtension();
            $path = $data['icon']->storeAs('categories', $filename, 'r2');
            $data['icon_url'] = Storage::disk('r2')->url($path);
            unset($data['icon']);
        }

        return $this->categoryRepository->update($id, $data);
    }

    /**
     * @inheritDoc
     */
    public function deleteCategory(int $id): bool
    {
        $category = $this->getCategoryById($id);
        if ($category->icon_url) {
            $this->deleteFileFromR2($category->icon_url);
        }

        return $this->categoryRepository->delete($id);
    }

    /**
     * Delete file from R2 by URL.
     *
     * @param string $url
     * @return void
     */
    protected function deleteFileFromR2(string $url): void
    {
        $baseUrl = Storage::disk('r2')->url('');
        $path = str_replace($baseUrl, '', $url);
        $path = ltrim($path, '/');
        
        if ($path) {
            Storage::disk('r2')->delete($path);
        }
    }
}
