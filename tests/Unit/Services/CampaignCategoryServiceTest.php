<?php

namespace Tests\Unit\Services;

use App\Models\CampaignCategory;
use App\Repositories\Interfaces\CampaignCategoryRepositoryInterface;
use App\Services\Implementations\CampaignCategoryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Illuminate\Pagination\LengthAwarePaginator;

class CampaignCategoryServiceTest extends TestCase
{
    public function test_create_category_uploads_to_r2()
    {
        Storage::fake('r2');
        $file = UploadedFile::fake()->image('icon.png');
        
        $data = [
            'name' => 'Test Category',
            'icon' => $file
        ];

        $capturedData = [];
        $this->mock(CampaignCategoryRepositoryInterface::class, function (MockInterface $mock) use (&$capturedData) {
            $mock->shouldReceive('create')
                ->once()
                ->with(Mockery::on(function($arg) use (&$capturedData) {
                    $capturedData = $arg;
                    return $arg['name'] === 'Test Category' && isset($arg['icon_url']);
                }))
                ->andReturn(new CampaignCategory());
        });

        $service = app(CampaignCategoryService::class);
        $service->createCategory($data);
        
        $filename = basename($capturedData['icon_url']);
        Storage::disk('r2')->assertExists('categories/' . $filename);
    }

    public function test_get_all_categories_returns_paginated_data()
    {
        $perPage = 10;
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mock(CampaignCategoryRepositoryInterface::class, function (MockInterface $mock) use ($mockPaginator, $perPage) {
            $mock->shouldReceive('getAllPaginated')->once()->with($perPage)->andReturn($mockPaginator);
        });

        $service = app(CampaignCategoryService::class);
        $result = $service->getAllCategories($perPage);

        $this->assertSame($mockPaginator, $result);
    }

    public function test_delete_category_calls_repository_and_removes_file()
    {
        Storage::fake('r2');
        $path = 'categories/test.png';
        Storage::disk('r2')->put($path, 'content');
        $url = Storage::disk('r2')->url($path);

        $category = new CampaignCategory(['id' => 1, 'icon_url' => $url]);

        $this->mock(CampaignCategoryRepositoryInterface::class, function (MockInterface $mock) use ($category) {
            $mock->shouldReceive('findById')->andReturn($category);
            $mock->shouldReceive('delete')->once()->with(1)->andReturn(true);
        });

        $service = app(CampaignCategoryService::class);
        $service->deleteCategory(1);

        Storage::disk('r2')->assertMissing($path);
    }
}
