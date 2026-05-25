<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DonationReceivedNotification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_notifications()
    {
        $user = User::factory()->create();
        
        // Mock a notification being sent to the user
        $donation = \App\Models\Donation::factory()->create();
        $user->notify(new DonationReceivedNotification($donation));

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/auth/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta', 'message']);
            
        $this->assertCount(1, $response->json('data'));
    }

    public function test_user_can_mark_notification_as_read()
    {
        $user = User::factory()->create();
        $donation = \App\Models\Donation::factory()->create();
        $user->notify(new DonationReceivedNotification($donation));

        $notificationId = $user->unreadNotifications->first()->id;

        $response = $this->actingAs($user, 'api')
            ->patchJson("/api/auth/notifications/{$notificationId}/read");

        $response->assertStatus(200);
        $this->assertCount(0, $user->fresh()->unreadNotifications);
    }

    public function test_user_cannot_read_others_notification()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        
        $donation = \App\Models\Donation::factory()->create();
        $userB->notify(new DonationReceivedNotification($donation));
        $notificationId = $userB->notifications->first()->id;

        $response = $this->actingAs($userA, 'api')
            ->patchJson("/api/auth/notifications/{$notificationId}/read");

        $response->assertStatus(404);
        $this->assertCount(1, $userB->fresh()->unreadNotifications);
    }

    public function test_user_can_get_only_unread_notifications()
    {
        $user = User::factory()->create();
        $donation = \App\Models\Donation::factory()->create();
        
        // Send 2 notifications
        $user->notify(new DonationReceivedNotification($donation));
        $user->notify(new DonationReceivedNotification($donation));
        
        // Mark 1 as read
        $user->unreadNotifications->first()->markAsRead();

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/auth/notifications/unread');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_user_can_mark_all_notifications_as_read()
    {
        $user = User::factory()->create();
        $donation = \App\Models\Donation::factory()->create();
        
        $user->notify(new DonationReceivedNotification($donation));
        $user->notify(new DonationReceivedNotification($donation));
        
        $this->assertCount(2, $user->unreadNotifications);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/notifications/read-all');

        $response->assertStatus(200);
        $this->assertCount(0, $user->fresh()->unreadNotifications);
    }
}
