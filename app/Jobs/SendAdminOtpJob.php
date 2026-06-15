<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Resend\Laravel\Facades\Resend;
use Illuminate\Support\Facades\View;

class SendAdminOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $email,
        public string $otp
    ) {}

    public function handle(): void
    {
        Resend::emails()->send([
            'from' => 'Fundraiser <onboarding@resend.dev>',
            'to' => $this->email,
            'subject' => 'Admin Login OTP',
            'html' => View::make('emails.admin-otp', ['otp' => $this->otp])->render(),
        ]);
    }
}
