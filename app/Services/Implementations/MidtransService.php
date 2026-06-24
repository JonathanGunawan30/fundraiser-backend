<?php

namespace App\Services\Implementations;

use App\Models\Donation;
use App\Models\User;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Create Midtrans Snap Token.
     */
    public function createSnapToken(Donation $donation, ?User $user = null): object
    {
        Log::info('Generating Midtrans Snap Token', [
            'donation_number' => $donation->donation_number,
            'amount' => $donation->amount,
            'user_id' => $user ? $user->id : null,
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $donation->donation_number,
                'gross_amount' => (int) $donation->amount,
            ],
            'customer_details' => [
                'first_name' => $user ? $user->name : 'Donatur',
                'email' => $user ? $user->email : 'donatur@anonymous.com',
            ],
            'callbacks' => [
                'finish' => config('midtrans.redirect_url'),
            ],
        ];

        return Snap::createTransaction($params);
    }
}
