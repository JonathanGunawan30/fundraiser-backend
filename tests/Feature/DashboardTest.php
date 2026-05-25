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
                    ]
                ]
            ]);
    }
}
