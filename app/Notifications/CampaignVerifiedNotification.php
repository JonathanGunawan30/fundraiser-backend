<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Campaign;

class CampaignVerifiedNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Campaign $campaign)
    {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusText = $this->campaign->verified_status === 'approved' ? 'disetujui' : 'ditolak';
        
        return [
            'type' => 'campaign_verified',
            'campaign_id' => $this->campaign->id,
            'campaign_title' => $this->campaign->title,
            'status' => $this->campaign->verified_status,
            'message' => 'Campaign Anda "' . $this->campaign->title . '" telah ' . $statusText . ' oleh Admin.',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => $this->toArray($notifiable),
            'read_at' => null,
            'created_at' => now(),
        ]);
    }
}
