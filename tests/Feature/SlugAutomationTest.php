<?php

namespace Tests\Feature;

use App\Models\CampaignCategory;
use App\Models\Tag;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlugAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_slug_is_automatically_generated()
    {
        $category = CampaignCategory::create([
            'name' => 'Bencana Alam & Kemanusiaan',
        ]);

        $this->assertEquals('bencana-alam-kemanusiaan', $category->slug);
    }

    public function test_category_slug_handles_collisions()
    {
        CampaignCategory::create(['name' => 'Test']);
        $category2 = CampaignCategory::create(['name' => 'Test']);

        $this->assertEquals('test-1', $category2->slug);
    }

    public function test_tag_slug_is_automatically_generated()
    {
        $tag = Tag::create(['name' => 'Mendesak Sekali']);
        $this->assertEquals('mendesak-sekali', $tag->slug);
    }

    public function test_campaign_slug_is_generated_from_title()
    {
        $user = User::factory()->create();
        $category = CampaignCategory::create(['name' => 'Cat']);
        
        $campaign = Campaign::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Bantu Korban Banjir 2026',
            'description' => 'Desc',
            'story' => 'Story',
            'goal_amount' => 1000000,
            'cover_image_url' => 'http://test.com/image.jpg'
        ]);

        $this->assertEquals('bantu-korban-banjir-2026', $campaign->slug);
    }

    public function test_slug_updates_when_source_field_changes()
    {
        $tag = Tag::create(['name' => 'Old Name']);
        $tag->update(['name' => 'New Name']);

        $this->assertEquals('new-name', $tag->slug);
    }
}
