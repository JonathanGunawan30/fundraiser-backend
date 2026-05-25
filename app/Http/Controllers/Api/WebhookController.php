<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\DonationServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(protected DonationServiceInterface $donationService)
    {}

    public function handleMidtrans(Request $request)
    {
        $payload = $request->all();
        Log::info('Midtrans Webhook Received', $payload);

        // Verification of signature is recommended for production
        // Snap::createTransaction doesn't give us the signature back, 
        // but Midtrans sends it in the webhook.
        
        $success = $this->donationService->handleMidtransNotification($payload);

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook processed'
        ]);
    }
}
