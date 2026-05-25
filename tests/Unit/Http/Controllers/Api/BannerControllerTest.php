<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\Admin;
use App\Models\Banner;
use App\Services\Interfaces\BannerServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
use Mockery;
use Tests\TestCase;

class BannerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_success_response()
    {
        $this->mock(BannerServiceInterface::class, function (MockInterface $mock) {
            $paginator = new LengthAwarePaginator(collect([]), 0, 10);
            $mock->shouldReceive('getAllBanners')->once()->with(10)->andReturn($paginator);
        });

        $response = $this->getJson('/api/banners?per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Banners retrieved successfully'
            ]);
    }

    public function test_show_returns_success_response()
    {
        $banner = new Banner(['title' => 'Test Banner', 'image_url' => 'https://r2.com/test.jpg']);
        $banner->id = 1;

        $this->mock(BannerServiceInterface::class, function (MockInterface $mock) use ($banner) {
            $mock->shouldReceive('getBannerById')->once()->with(1)->andReturn($banner);
        });

        $response = $this->getJson('/api/banners/1');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => 1,
                    'title' => 'Test Banner'
                ]
            ]);
    }

    public function test_store_returns_success_response()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        
        $bannerData = [
            'title' => 'New Banner',
            'is_active' => true,
        ];
        
        $banner = new Banner(array_merge(['id' => 1, 'image_url' => 'https://r2.com/new.jpg'], $bannerData));
        $banner->id = 1;

        $this->mock(BannerServiceInterface::class, function (MockInterface $mock) use ($banner) {
            $mock->shouldReceive('createBanner')->once()->andReturn($banner);
        });

        // Use UploadedFile to satisfy request validation
        $file = \Illuminate\Http\UploadedFile::fake()->image('banner.jpg');
        
        $response = $this->actingAs($admin, 'admin-api')
            ->postJson('/api/admin/banners', array_merge($bannerData, ['image' => $file]));

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Banner created successfully',
                'data' => ['title' => 'New Banner']
            ]);
    }

    public function test_update_returns_success_response()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        
        $bannerData = ['title' => 'Updated Banner'];
        $banner = new Banner(array_merge(['id' => 1, 'image_url' => 'https://r2.com/updated.jpg'], $bannerData));
        $banner->id = 1;

        $this->mock(BannerServiceInterface::class, function (MockInterface $mock) use ($banner) {
            $mock->shouldReceive('updateBanner')->once()->with(1, Mockery::any())->andReturn($banner);
        });

        $response = $this->actingAs($admin, 'admin-api')
            ->putJson('/api/admin/banners/1', $bannerData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Banner updated successfully',
                'data' => ['title' => 'Updated Banner']
            ]);
    }

    public function test_destroy_returns_success_response()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->mock(BannerServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('deleteBanner')->once()->with(1)->andReturn(true);
        });

        $response = $this->actingAs($admin, 'admin-api')
            ->deleteJson('/api/admin/banners/1');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Banner deleted successfully'
            ]);
    }

    public function test_search_returns_success_response()
    {
        $this->mock(BannerServiceInterface::class, function (MockInterface $mock) {
            $paginator = new LengthAwarePaginator(collect([]), 0, 10);
            $mock->shouldReceive('searchBanners')->once()->with('test', 10)->andReturn($paginator);
        });

        $response = $this->getJson('/api/banners/search?keyword=test&per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Banners search results retrieved successfully'
            ]);
    }
}
