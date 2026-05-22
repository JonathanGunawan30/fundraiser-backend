<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_site_settings()
    {
        SiteSetting::factory()->count(5)->create();

        $response = $this->getJson('/api/site-settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'key', 'value', 'type', 'updated_at']
                ]
            ]);
    }

    public function test_admin_can_create_site_setting()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $data = [
            'key' => 'site_name',
            'value' => 'Fundraiser',
            'type' => 'string',
        ];

        $response = $this->actingAs($admin, 'admin-api')
            ->postJson('/api/auth/site-settings', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Site setting created successfully',
                'data' => ['key' => 'site_name']
            ]);

        $this->assertDatabaseHas('site_settings', ['key' => 'site_name']);
    }

    public function test_admin_can_update_site_setting()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $setting = SiteSetting::factory()->create(['key' => 'site_logo', 'value' => 'logo.png']);

        $data = [
            'value' => 'new_logo.png',
        ];

        $response = $this->actingAs($admin, 'admin-api')
            ->putJson("/api/auth/site-settings/{$setting->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Site setting updated successfully',
                'data' => ['value' => 'new_logo.png']
            ]);

        $this->assertDatabaseHas('site_settings', ['id' => $setting->id, 'value' => 'new_logo.png']);
    }

    public function test_admin_can_delete_site_setting()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $setting = SiteSetting::factory()->create();

        $response = $this->actingAs($admin, 'admin-api')
            ->deleteJson("/api/auth/site-settings/{$setting->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Site setting deleted successfully'
            ]);

        $this->assertDatabaseMissing('site_settings', ['id' => $setting->id]);
    }
}
