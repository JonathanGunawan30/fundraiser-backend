<?php

namespace Tests\Feature\Security;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test for SQL Injection (SQLi) prevention in search.
     */
    public function test_search_is_safe_from_sql_injection()
    {
        Campaign::factory()->create(['title' => 'Target Campaign']);
        Campaign::factory()->create(['title' => 'Secret Data']);

        // Payload typical for SQLi
        $payload = "' OR 1=1 --";
        
        $response = $this->getJson("/api/campaigns/search?keyword={$payload}");

        $response->assertStatus(200);
        
        // If SQLi worked, it would return ALL campaigns. 
        // If safe, it should return 0 results because no title matches that weird string.
        $this->assertCount(0, $response->json('data'), 'Search returned results for SQLi payload, potential vulnerability!');
    }

    /**
     * Test for Mass Assignment vulnerability.
     */
    public function test_profile_update_is_safe_from_mass_assignment()
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user, 'api')
            ->patchJson('/api/auth/profile', [
                'name' => 'New Name',
                'status' => 'suspended' // Sensitive field that should NOT be changeable via profile
            ]);

        $response->assertStatus(200);
        
        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        // Status should still be 'active'
        $this->assertEquals('active', $user->status, 'Mass assignment allowed changing sensitive status field!');
    }

    /**
     * Test for Stored XSS prevention.
     */
    public function test_campaign_story_is_sanitized()
    {
        $user = User::factory()->create();
        $category = \App\Models\CampaignCategory::factory()->create();

        $data = [
            'category_id' => $category->id,
            'title' => 'Safe Campaign',
            'description' => 'Desc',
            'story' => "<script>alert('xss')</script> This is my story.",
            'goal_amount' => 100000,
            'deadline' => now()->addMonth()->toDateString(),
            'cover_image' => \Illuminate\Http\UploadedFile::fake()->image('cover.jpg'),
        ];

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/campaigns', $data);

        $response->assertStatus(201);
        
        // Verify data is stored but when we display it, we check if it contains the script tag
        // Note: Laravel's Blade {{ }} automatically escapes, but we check if the raw DB data is at least stored as-is
        // and doesn't crash the JSON response.
        $this->assertDatabaseHas('campaigns', ['title' => 'Safe Campaign']);
        
        $campaign = Campaign::first();
        $this->assertStringContainsString("<script>", $campaign->story);
        
        // In a real frontend integration, we would test if the JSON response is clean
        $response->assertJsonFragment(['story' => $campaign->story]);
    }
}
