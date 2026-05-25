<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\CampaignUpdate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CampaignUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_campaign_updates()
    {
        CampaignUpdate::factory()->count(3)->create();

        $response = $this->getJson('/api/campaign-updates');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta', 'message']);
    }

    public function test_user_can_create_update_for_their_campaign()
    {
        Storage::fake('r2');
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $data = [
            'campaign_id' => $campaign->id,
            'title' => 'Progress Update 1',
            'content' => 'Everything is going well.',
            'image' => UploadedFile::fake()->image('update.jpg')
        ];

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/campaign-updates', $data);

        $response->assertStatus(201);
        
        $update = CampaignUpdate::first();
        $this->assertEquals('Progress Update 1', $update->title);
        $this->assertNotNull($update->image_url);
        
        $filename = basename($update->image_url);
        Storage::disk('r2')->assertExists('campaigns/updates/' . $filename);
    }

    public function test_user_cannot_create_update_for_others_campaign()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $otherUser->id]);

        $data = [
            'campaign_id' => $campaign->id,
            'title' => 'Hack Update',
            'content' => 'I am a hacker.'
        ];

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/campaign-updates', $data);

        $response->assertStatus(403);
    }

    public function test_user_can_update_their_own_update()
    {
        Storage::fake('r2');
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);
        $update = CampaignUpdate::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'title' => 'Old Title'
        ]);

        $data = [
            'title' => 'New Title',
            'image' => UploadedFile::fake()->image('new_update.jpg')
        ];

        $response = $this->actingAs($user, 'api')
            ->putJson("/api/auth/campaign-updates/{$update->id}", $data);

        $response->assertStatus(200);
        $update->refresh();
        $this->assertEquals('New Title', $update->title);
    }

    public function test_user_can_delete_their_own_update()
    {
        Storage::fake('r2');
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);
        $update = CampaignUpdate::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user, 'api')
            ->deleteJson("/api/auth/campaign-updates/{$update->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('campaign_updates', ['id' => $update->id]);
    }
}
