<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Campaign;
use App\Models\Withdrawal;
use App\Notifications\CampaignSubmittedNotification;
use App\Notifications\WithdrawalRequestedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_receives_notification_when_campaign_submitted()
    {
        $admin = Admin::factory()->create(['is_active' => true]);

        // Trigger store/create in CampaignService via Repository or just directly dispatch the logic.
        // Actually, we'll create the campaign using the service to trigger the logic.
        $campaignService = app(\App\Services\Interfaces\CampaignServiceInterface::class);
        $user = \App\Models\User::factory()->create();
        
        $campaign = $campaignService->createCampaign([
            'title' => 'Test Campaign',
            'slug' => 'test-campaign',
            'short_description' => 'Short desc',
            'description' => 'Full desc',
            'story' => 'Full story details',
            'cover_image_url' => 'https://via.placeholder.com/600',
            'goal_amount' => 10000000,
            'status' => 'pending',
            'user_id' => $user->id,
            'category_id' => \App\Models\CampaignCategory::factory()->create()->id,
        ]);

        $this->assertCount(1, $admin->fresh()->notifications);
        $notification = $admin->fresh()->notifications->first();
        $this->assertEquals('campaign_submitted', $notification->data['type']);
    }

    public function test_admin_receives_notification_when_withdrawal_requested()
    {
        $admin = Admin::factory()->create(['is_active' => true]);
        
        $user = \App\Models\User::factory()->create();
        $campaign = Campaign::factory()->create([
            'status' => 'active',
            'collected_amount' => 5000000,
            'user_id' => $user->id,
        ]);
        
        $withdrawalService = app(\App\Services\Interfaces\WithdrawalServiceInterface::class);
        
        $withdrawal = $withdrawalService->requestWithdrawal([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'amount' => 1000000,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'John Doe',
        ]);

        $this->assertCount(1, $admin->fresh()->notifications);
        $notification = $admin->fresh()->notifications->first();
        $this->assertEquals('withdrawal_requested', $notification->data['type']);
    }

    public function test_admin_can_get_notifications()
    {
        $admin = Admin::factory()->create(['is_active' => true]);
        $campaign = Campaign::factory()->create();
        $admin->notify(new CampaignSubmittedNotification($campaign));

        $response = $this->actingAs($admin, 'admin-api')
            ->getJson('/api/admin/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta', 'message']);
            
        $this->assertCount(1, $response->json('data'));
    }

    public function test_admin_can_mark_notification_as_read()
    {
        $admin = Admin::factory()->create(['is_active' => true]);
        $campaign = Campaign::factory()->create();
        $admin->notify(new CampaignSubmittedNotification($campaign));

        $notificationId = $admin->unreadNotifications->first()->id;

        $response = $this->actingAs($admin, 'admin-api')
            ->patchJson("/api/admin/notifications/{$notificationId}/read");

        $response->assertStatus(200);
        $this->assertCount(0, $admin->fresh()->unreadNotifications);
    }

    public function test_admin_can_get_only_unread_notifications()
    {
        $admin = Admin::factory()->create(['is_active' => true]);
        $campaign = Campaign::factory()->create();
        
        $admin->notify(new CampaignSubmittedNotification($campaign));
        $admin->notify(new CampaignSubmittedNotification($campaign));
        
        $admin->unreadNotifications->first()->markAsRead();

        $response = $this->actingAs($admin, 'admin-api')
            ->getJson('/api/admin/notifications/unread');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_admin_can_mark_all_notifications_as_read()
    {
        $admin = Admin::factory()->create(['is_active' => true]);
        $campaign = Campaign::factory()->create();
        
        $admin->notify(new CampaignSubmittedNotification($campaign));
        $admin->notify(new CampaignSubmittedNotification($campaign));

        $response = $this->actingAs($admin, 'admin-api')
            ->postJson('/api/admin/notifications/read-all');

        $response->assertStatus(200);
        $this->assertCount(0, $admin->fresh()->unreadNotifications);
    }
}
