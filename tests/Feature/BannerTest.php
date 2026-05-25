<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_banners()
    {
        Banner::create([
            'title' => 'Banner 1',
            'image_url' => 'https://example.com/1.jpg',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/banners');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'image_url', 'is_active']
                ]
            ]);
    }

    public function test_admin_can_create_banner_with_r2_upload()
    {
        Storage::fake('r2');
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $file = UploadedFile::fake()->image('banner.jpg');
        $data = [
            'title' => 'Promo Banner',
            'image' => $file,
            'is_active' => true,
        ];

        $response = $this->actingAs($admin, 'admin-api')
            ->postJson('/api/admin/banners', $data);

        $response->assertStatus(201);
        
        $banner = Banner::first();
        $this->assertNotNull($banner->image_url);
        $this->assertStringContainsString('banners/', $banner->image_url);
        
        // Extract filename from URL since it's now a UUID
        $filename = basename($banner->image_url);
        Storage::disk('r2')->assertExists('banners/' . $filename);
    }

    public function test_admin_can_update_banner_with_new_image()
    {
        Storage::fake('r2');
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $banner = Banner::create([
            'title' => 'Old Title',
            'image_url' => 'https://example.com/old.jpg',
        ]);

        $file = UploadedFile::fake()->image('new_banner.jpg');
        $data = [
            'title' => 'New Title',
            'image' => $file,
        ];

        $response = $this->actingAs($admin, 'admin-api')
            ->putJson("/api/admin/banners/{$banner->id}", $data);

        $response->assertStatus(200);
        
        $banner->refresh();
        $this->assertEquals('New Title', $banner->title);
        $this->assertStringContainsString('.jpg', $banner->image_url);
        
        $filename = basename($banner->image_url);
        Storage::disk('r2')->assertExists('banners/' . $filename);
    }

    public function test_admin_can_delete_banner_and_image_from_r2()
    {
        Storage::fake('r2');
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $path = 'banners/test.jpg';
        Storage::disk('r2')->put($path, 'content');
        $url = Storage::disk('r2')->url($path);

        $banner = Banner::create([
            'title' => 'To be deleted',
            'image_url' => $url,
        ]);

        $response = $this->actingAs($admin, 'admin-api')
            ->deleteJson("/api/admin/banners/{$banner->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
        Storage::disk('r2')->assertMissing($path);
    }
}
