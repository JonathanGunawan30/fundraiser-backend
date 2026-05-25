<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Models\Admin;
use App\Models\SiteSetting;
use App\Services\Interfaces\SiteSettingServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
use Mockery;
use Tests\TestCase;

class SiteSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_success_response()
    {
        $this->mock(SiteSettingServiceInterface::class, function (MockInterface $mock) {
            $paginator = new LengthAwarePaginator(collect([]), 0, 10);
            $mock->shouldReceive('getAllSiteSettings')->once()->with(10)->andReturn($paginator);
        });

        $response = $this->getJson('/api/site-settings?per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Site settings retrieved successfully'
            ]);
    }

    public function test_store_returns_success_response()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        
        $setting = new SiteSetting(['id' => 1, 'key' => 'site_name', 'value' => 'Fundraiser', 'type' => 'string']);
        $setting->id = 1;

        $this->mock(SiteSettingServiceInterface::class, function (MockInterface $mock) use ($setting) {
            $mock->shouldReceive('createSiteSetting')->once()->andReturn($setting);
        });

        $response = $this->actingAs($admin, 'admin-api')
            ->postJson('/api/admin/site-settings', [
                'key' => 'site_name',
                'value' => 'Fundraiser',
                'type' => 'string'
            ]);

        $response->assertStatus(201);
    }
}
