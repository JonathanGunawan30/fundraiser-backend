<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Campaign;
use App\Models\CampaignCategory;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_campaigns()
    {
        Campaign::factory()->count(3)->create();

        $response = $this->getJson('/api/campaigns');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta', 'message']);
    }

    public function test_user_can_create_campaign_with_images_and_tags()
    {
        Storage::fake('r2');
        $user = User::factory()->create();
        $category = CampaignCategory::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $data = [
            'category_id' => $category->id,
            'title' => 'Help the Children',
            'description' => 'A short description about helping children.',
            'story' => 'A long story about why we need help.',
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
            'goal_amount' => 500000,
            'deadline' => now()->addMonth()->toDateString(),
            'tags' => $tags->pluck('id')->toArray(),
            'images' => [
                UploadedFile::fake()->image('gallery1.jpg'),
                UploadedFile::fake()->image('gallery2.jpg'),
            ]
        ];

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/campaigns', $data);

        $response->assertStatus(201);
        
        $campaign = Campaign::first();
        $this->assertEquals('Help the Children', $campaign->title);
        $this->assertCount(2, $campaign->tags);
        $this->assertCount(2, $campaign->images);
        
        $coverName = basename($campaign->cover_image_url);
        Storage::disk('r2')->assertExists('campaigns/covers/' . $coverName);
    }

    public function test_admin_can_verify_campaign()
    {
        \Illuminate\Support\Facades\Notification::fake();
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['verified_status' => 'pending', 'user_id' => $user->id]);

        $response = $this->actingAs($admin, 'admin-api')
            ->postJson("/api/admin/campaigns/{$campaign->id}/verify", [
                'status' => 'approved'
            ]);

        $response->assertStatus(200);
        $campaign->refresh();
        $this->assertEquals('approved', $campaign->verified_status);
        $this->assertEquals('active', $campaign->status);
        $this->assertEquals($admin->id, $campaign->verified_by);

        \Illuminate\Support\Facades\Notification::assertSentTo(
            $user,
            \App\Notifications\CampaignVerifiedNotification::class
        );
    }

    public function test_user_can_update_campaign()
    {
        Storage::fake('r2');
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $data = [
            'title' => 'Updated Title',
            'goal_amount' => 1000000,
            'cover_image' => UploadedFile::fake()->image('new_cover.jpg'),
        ];

        $response = $this->actingAs($user, 'api')
            ->putJson("/api/auth/campaigns/{$campaign->id}", $data);

        $response->assertStatus(200);
        $campaign->refresh();
        $this->assertEquals('Updated Title', $campaign->title);
        $this->assertStringContainsString('.jpg', $campaign->cover_image_url);
    }

    public function test_user_can_delete_campaign()
    {
        Storage::fake('r2');
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id, 'collected_amount' => 0]);

        $response = $this->actingAs($user, 'api')
            ->deleteJson("/api/auth/campaigns/{$campaign->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
    }

    public function test_user_cannot_delete_campaign_with_donations()
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'collected_amount' => 100000 // has donations
        ]);

        $response = $this->actingAs($user, 'api')
            ->deleteJson("/api/auth/campaigns/{$campaign->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('campaigns', ['id' => $campaign->id]);
    }

    public function test_user_cannot_lower_goal_below_collected()
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'goal_amount' => 1000000,
            'collected_amount' => 500000
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson("/api/auth/campaigns/{$campaign->id}", [
                'goal_amount' => 400000 // lower than collected
            ]);

        $response->assertStatus(422);
    }
}
