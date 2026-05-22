<?php

namespace Tests\Unit\Services;

use App\Models\Banner;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use App\Services\Implementations\BannerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Illuminate\Pagination\LengthAwarePaginator;

class BannerServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_create_banner_uploads_to_r2()
    {
        Storage::fake('r2');
        $file = UploadedFile::fake()->image('banner.png');
        
        $data = [
            'title' => 'Test Banner',
            'image' => $file
        ];

        $capturedData = [];
        $this->mock(BannerRepositoryInterface::class, function (MockInterface $mock) use (&$capturedData) {
            $mock->shouldReceive('create')
                ->once()
                ->with(Mockery::on(function($arg) use (&$capturedData) {
                    $capturedData = $arg;
                    return $arg['title'] === 'Test Banner' && isset($arg['image_url']);
                }))
                ->andReturn(new Banner());
        });

        $service = app(BannerService::class);
        $service->createBanner($data);
        
        $filename = basename($capturedData['image_url']);
        Storage::disk('r2')->assertExists('banners/' . $filename);
    }

    public function test_get_all_banners_returns_paginated_data()
    {
        $perPage = 10;
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mock(BannerRepositoryInterface::class, function (MockInterface $mock) use ($mockPaginator, $perPage) {
            $mock->shouldReceive('getAllPaginated')
                ->once()
                ->with($perPage)
                ->andReturn($mockPaginator);
        });

        $service = app(BannerService::class);
        $result = $service->getAllBanners($perPage);

        $this->assertSame($mockPaginator, $result);
    }

    public function test_get_banner_by_id_returns_banner()
    {
        $bannerId = 1;
        $mockBanner = new Banner(['id' => $bannerId]);

        $this->mock(BannerRepositoryInterface::class, function (MockInterface $mock) use ($bannerId, $mockBanner) {
            $mock->shouldReceive('findById')
                ->once()
                ->with($bannerId)
                ->andReturn($mockBanner);
        });

        $service = app(BannerService::class);
        $result = $service->getBannerById($bannerId);

        $this->assertSame($mockBanner, $result);
    }

    public function test_update_banner_calls_repository()
    {
        $bannerId = 1;
        $data = ['title' => 'Updated Title'];
        $mockBanner = new Banner(array_merge(['id' => $bannerId], $data));

        $this->mock(BannerRepositoryInterface::class, function (MockInterface $mock) use ($bannerId, $data, $mockBanner) {
            $mock->shouldReceive('update')
                ->once()
                ->with($bannerId, $data)
                ->andReturn($mockBanner);
        });

        $service = app(BannerService::class);
        $result = $service->updateBanner($bannerId, $data);

        $this->assertSame($mockBanner, $result);
    }

    public function test_delete_banner_calls_repository()
    {
        $bannerId = 1;
        $mockBanner = new Banner(['id' => $bannerId, 'image_url' => null]);

        $this->mock(BannerRepositoryInterface::class, function (MockInterface $mock) use ($bannerId, $mockBanner) {
            $mock->shouldReceive('findById')->andReturn($mockBanner);
            $mock->shouldReceive('delete')->once()->with($bannerId)->andReturn(true);
        });

        $service = app(BannerService::class);
        $result = $service->deleteBanner($bannerId);

        $this->assertTrue($result);
    }

    public function test_search_banners_calls_repository()
    {
        $keyword = 'promo';
        $perPage = 10;
        $mockPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->mock(BannerRepositoryInterface::class, function (MockInterface $mock) use ($keyword, $perPage, $mockPaginator) {
            $mock->shouldReceive('search')
                ->once()
                ->with($keyword, $perPage)
                ->andReturn($mockPaginator);
        });

        $service = app(BannerService::class);
        $result = $service->searchBanners($keyword, $perPage);

        $this->assertSame($mockPaginator, $result);
    }
}
