<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Donation;

class DonationReceivedNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Donation $donation)
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
        return [
            'type' => 'donation_received',
            'campaign_id' => $this->donation->campaign_id,
            'campaign_title' => $this->donation->campaign->title,
            'donation_id' => $this->donation->id,
            'amount' => $this->donation->amount,
            'donor_name' => $this->donation->is_anonymous ? 'Anonim' : ($this->donation->user->name ?? 'Anonim'),
            'message' => 'Anda menerima donasi baru sebesar Rp ' . number_format($this->donation->amount) . ' untuk campaign ' . $this->donation->campaign->title,
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
