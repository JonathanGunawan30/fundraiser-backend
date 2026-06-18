<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Resend\Laravel\Facades\Resend;
use Illuminate\Support\Facades\View;

class SendCampaignStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $email,
        public string $userName,
        public string $campaignTitle,
        public string $status
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $statusLabel = $this->status === 'approved' ? 'Disetujui' : 'Ditolak';

        Resend::emails()->send([
            'from' => 'Fundraiser <onboarding@resend.dev>',
            'to' => $this->email,
            'subject' => 'Update Status Campaign: ' . $this->campaignTitle,
            'html' => View::make('emails.campaign-status', [
                'userName' => $this->userName,
                'campaignTitle' => $this->campaignTitle,
                'status' => $statusLabel
            ])->render(),
        ]);
    }
}
