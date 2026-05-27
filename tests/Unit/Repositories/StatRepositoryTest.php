<?php

namespace Tests\Unit\Repositories;

use App\Models\Campaign;
use App\Models\CampaignCategory;
use App\Models\Donation;
use App\Models\User;
use App\Repositories\Implementations\StatRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $statRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statRepository = new StatRepository();
    }

    public function test_get_global_stats_calculates_correctly()
    {
        User::factory()->count(2)->create();
        Campaign::factory()->create(['status' => 'active']);
        Campaign::factory()->create(['status' => 'pending']);
        
        Donation::factory()->create(['status' => 'success', 'amount' => 1000]);
        Donation::factory()->create(['status' => 'success', 'amount' => 2000]);
        Donation::factory()->create(['status' => 'pending', 'amount' => 5000]);

        $stats = $this->statRepository->getGlobalStats();

        $this->assertEquals(3000, $stats['total_donations_amount']);
        $this->assertEquals(2, $stats['total_donations_count']);
        $this->assertEquals(1, $stats['total_campaigns_active']);
    }

    public function test_get_category_distribution()
    {
        $cat1 = CampaignCategory::factory()->create(['name' => 'Cat A']);
        $cat2 = CampaignCategory::factory()->create(['name' => 'Cat B']);

        Campaign::factory()->count(2)->create(['category_id' => $cat1->id]);
        Campaign::factory()->count(1)->create(['category_id' => $cat2->id]);

        $dist = $this->statRepository->getCategoryDistribution();

        $this->assertCount(2, $dist);
        $this->assertEquals('Cat A', $dist[0]['label']);
        $this->assertEquals(2, $dist[0]['value']);
    }
}
