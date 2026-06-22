<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard_stats()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@dashboard.com',
            'password' => bcrypt('password'),
        ]);

        // Create some data
        Campaign::factory()->count(2)->create(['status' => 'active']);
        Donation::factory()->count(3)->create(['status' => 'success', 'amount' => 100000]);

        $response = $this->actingAs($admin, 'admin-api')
            ->getJson('/api/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'overview' => [
                        'total_donations_amount',
                        'total_donations_count',
                        'total_campaigns_active',
                        'total_users_count',
                    ],
                    'charts' => [
                        'donations_last_30_days',
                        'category_distribution',
                    ],
                    'recent_activity'
                ]
            ]);
    }

    public function test_user_can_access_personal_dashboard_stats()
    {
        $user = User::factory()->create();
        
        // Create campaign for this user
        $campaign = Campaign::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        Donation::factory()->create(['campaign_id' => $campaign->id, 'status' => 'success', 'amount' => 50000]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/auth/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'overview' => [
                        'total_raised_amount' => 50000,
                        'total_donors_count' => 1,
                        'active_campaigns_count' => 1,
                        'total_collected_amount' => 50000,
                        'total_donations' => 0,
                        'total_donated_amount' => 0,
                    ]
                ]
            ]);
    }

    public function test_user_can_filter_dashboard_stats_by_days()
    {
        $user = User::factory()->create();
        
        // Create campaign for this user
        $campaign = Campaign::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        
        // Donation inside 7 days
        Donation::factory()->create([
            'campaign_id' => $campaign->id, 
            'status' => 'success', 
            'amount' => 50000,
            'created_at' => now()->subDays(2)
        ]);

        // Donation outside 7 days but inside 30 days
        Donation::factory()->create([
            'campaign_id' => $campaign->id, 
            'status' => 'success', 
            'amount' => 100000,
            'created_at' => now()->subDays(15)
        ]);

        // Donation made by the user inside 7 days
        $otherCampaign = Campaign::factory()->create(['status' => 'active']);
        Donation::factory()->create([
            'user_id' => $user->id,
            'campaign_id' => $otherCampaign->id,
            'status' => 'success',
            'amount' => 25000,
            'created_at' => now()->subDays(3)
        ]);

        // Donation made by the user outside 7 days but inside 30 days
        Donation::factory()->create([
            'user_id' => $user->id,
            'campaign_id' => $otherCampaign->id,
            'status' => 'success',
            'amount' => 75000,
            'created_at' => now()->subDays(20)
        ]);

        // Filter: 7 days
        $response7 = $this->actingAs($user, 'api')
            ->getJson('/api/auth/dashboard?days=7');

        $response7->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'overview' => [
                        'total_raised_amount' => 50000,
                        'total_donors_count' => 1,
                        'total_collected_amount' => 50000,
                        'total_donations' => 1,
                        'total_donated_amount' => 25000,
                    ]
                ]
            ]);

        // Filter: 30 days
        $response30 = $this->actingAs($user, 'api')
            ->getJson('/api/auth/dashboard?days=30');

        $response30->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'overview' => [
                        'total_raised_amount' => 150000,
                        'total_donors_count' => 2,
                        'total_collected_amount' => 150000,
                        'total_donations' => 2,
                        'total_donated_amount' => 100000,
                    ]
                ]
            ]);

        // Filter: all
        $responseAll = $this->actingAs($user, 'api')
            ->getJson('/api/auth/dashboard?days=all');

        $responseAll->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'overview' => [
                        'total_raised_amount' => 150000,
                        'total_donors_count' => 2,
                        'total_collected_amount' => 150000,
                        'total_donations' => 2,
                        'total_donated_amount' => 100000,
                    ]
                ]
            ]);
    }
}

