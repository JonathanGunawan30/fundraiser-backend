<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\Admin;
use App\Models\CampaignCategory;
use App\Services\Interfaces\CampaignCategoryServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
use Mockery;
use Tests\TestCase;

class CampaignCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_success_response()
    {
        $this->mock(CampaignCategoryServiceInterface::class, function (MockInterface $mock) {
            $paginator = new LengthAwarePaginator(collect([]), 0, 10);
            $mock->shouldReceive('getAllCategories')->once()->with(10)->andReturn($paginator);
        });

        $response = $this->getJson('/api/campaign-categories?per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Campaign categories retrieved successfully'
            ]);
    }

    public function test_show_returns_success_response()
    {
        $category = new CampaignCategory(['id' => 1, 'name' => 'Health', 'slug' => 'health']);
        $category->id = 1;

        $this->mock(CampaignCategoryServiceInterface::class, function (MockInterface $mock) use ($category) {
            $mock->shouldReceive('getCategoryById')->once()->with(1)->andReturn($category);
        });

        $response = $this->getJson('/api/campaign-categories/1');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => 1,
                    'name' => 'Health'
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
        
        $category = new CampaignCategory(['id' => 1, 'name' => 'Education', 'slug' => 'education']);
        $category->id = 1;

        $this->mock(CampaignCategoryServiceInterface::class, function (MockInterface $mock) use ($category) {
            $mock->shouldReceive('createCategory')->once()->andReturn($category);
        });

        $response = $this->actingAs($admin, 'admin-api')
            ->postJson('/api/admin/campaign-categories', [
                'name' => 'Education',
                'slug' => 'education'
            ]);

        $response->assertStatus(201);
    }
}
