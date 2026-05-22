<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CampaignCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CampaignCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories()
    {
        CampaignCategory::factory()->count(3)->create();

        $response = $this->getJson('/api/campaign-categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'icon_url', 'is_active', 'order_index']
                ]
            ]);
    }

    public function test_admin_can_create_category_with_r2_upload()
    {
        Storage::fake('r2');
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $file = UploadedFile::fake()->image('icon.png');
        $data = [
            'name' => 'Education',
            'slug' => 'education',
            'icon' => $file,
            'is_active' => true,
        ];

        $response = $this->actingAs($admin, 'admin-api')
            ->postJson('/api/auth/campaign-categories', $data);

        $response->assertStatus(201);
        
        $category = CampaignCategory::first();
        $this->assertNotNull($category->icon_url);
        
        $filename = basename($category->icon_url);
        Storage::disk('r2')->assertExists('categories/' . $filename);
    }

    public function test_admin_can_update_category_with_new_icon()
    {
        Storage::fake('r2');
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $category = CampaignCategory::factory()->create([
            'icon_url' => 'https://example.com/old.png'
        ]);

        $file = UploadedFile::fake()->image('new_icon.png');
        $data = [
            'name' => 'Updated Category',
            'icon' => $file,
        ];

        $response = $this->actingAs($admin, 'admin-api')
            ->putJson("/api/auth/campaign-categories/{$category->id}", $data);

        $response->assertStatus(200);
        
        $category->refresh();
        $this->assertEquals('Updated Category', $category->name);
        $this->assertStringContainsString('.png', $category->icon_url);
        
        $filename = basename($category->icon_url);
        Storage::disk('r2')->assertExists('categories/' . $filename);
    }

    public function test_admin_can_delete_category_and_icon_from_r2()
    {
        Storage::fake('r2');
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $path = 'categories/test.png';
        Storage::disk('r2')->put($path, 'content');
        $url = Storage::disk('r2')->url($path);

        $category = CampaignCategory::factory()->create([
            'icon_url' => $url,
        ]);

        $response = $this->actingAs($admin, 'admin-api')
            ->deleteJson("/api/auth/campaign-categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('campaign_categories', ['id' => $category->id]);
        Storage::disk('r2')->assertMissing($path);
    }
}
