<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_audit_logs()
    {
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'password' => bcrypt('password'),
        ]);

        // Trigger an activity by creating a campaign (if configured in model)
        // Or manual log
        activity()->log('Test Manual Log');

        $response = $this->actingAs($admin, 'admin-api')
            ->getJson('/api/admin/audit-logs');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta', 'message']);
            
        $this->assertCount(1, $response->json('data'));
    }

    public function test_campaign_verification_is_logged()
    {
        $admin = Admin::create([
            'name' => 'Verifier Admin',
            'email' => 'verifier@admin.com',
            'password' => bcrypt('password'),
        ]);

        $campaign = Campaign::factory()->create(['verified_status' => 'pending']);

        // Verify campaign
        $this->actingAs($admin, 'admin-api')
            ->postJson("/api/admin/campaigns/{$campaign->id}/verify", [
                'status' => 'approved'
            ]);

        // Check if activity was logged
        $response = $this->actingAs($admin, 'admin-api')
            ->getJson('/api/admin/audit-logs');

        $response->assertStatus(200);
        
        $descriptions = collect($response->json('data'))->pluck('description');
        $this->assertTrue($descriptions->contains('updated'), "Logs should contain an 'updated' entry. Found: " . $descriptions->implode(', '));
        
        $subjectTypes = collect($response->json('data'))->pluck('subject_type');
        $this->assertTrue($subjectTypes->contains('Campaign'));
    }
}
