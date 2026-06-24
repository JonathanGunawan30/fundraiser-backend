<?php

namespace App\Services\Implementations;

use App\Models\Donation;
use App\Repositories\Interfaces\DonationRepositoryInterface;
use App\Repositories\Interfaces\DonationPaymentRepositoryInterface;
use App\Services\Interfaces\DonationServiceInterface;
use App\Jobs\ProcessSuccessfulDonation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DonationService implements DonationServiceInterface
{
    public function __construct(
        protected DonationRepositoryInterface $donationRepository,
        protected DonationPaymentRepositoryInterface $paymentRepository,
        protected MidtransService $midtransService
    ) {}

    public function getAllDonations(int $perPage): LengthAwarePaginator
    {
        return $this->donationRepository->getAllPaginated($perPage);
    }

    public function getUserDonations(int $userId, int $perPage): LengthAwarePaginator
    {
        return $this->donationRepository->getByUserPaginated($userId, $perPage);
    }

    public function getDonationByNumber(string $number): Donation
    {
        $donation = $this->donationRepository->findByNumber($number);
        if (!$donation) {
            throw new ModelNotFoundException("Donation with number {$number} not found.");
        }
        return $donation;
    }

    public function createDonation(array $data): Donation
    {
        return DB::transaction(function () use ($data) {
            // 1. Generate Donation Number
            $data['donation_number'] = 'DON-' . strtoupper(Str::random(10));
            $data['status'] = 'pending';

            // 2. Save Donation
            $donation = $this->donationRepository->create($data);

            // 3. Get Midtrans Snap Token
            $user = auth('api')->user();
            $midtransResponse = $this->midtransService->createSnapToken($donation, $user);

            // 4. Save Payment Info
            $this->paymentRepository->create([
                'donation_id' => $donation->id,
                'gross_amount' => $donation->amount,
                'net_amount' => $donation->amount, // Will be updated on success
                'snap_token' => $midtransResponse->token,
                'payment_url' => $midtransResponse->redirect_url,
                'status' => 'pending'
            ]);

            Log::info('Donation created successfully, redirect snap URL generated', [
                'donation_number' => $donation->donation_number,
                'amount' => $donation->amount,
                'campaign_id' => $donation->campaign_id,
                'user_id' => $donation->user_id
            ]);

            return $donation->load('payment');
        });
    }

    public function handleMidtransNotification(array $payload): bool
    {
        $orderId = $payload['order_id'];
        $status = $payload['transaction_status'];
        $type = $payload['payment_type'];
        $fraud = $payload['fraud_status'];

        $donation = $this->getDonationByNumber($orderId);

        return DB::transaction(function () use ($donation, $status, $type, $fraud, $payload) {
            $donationStatus = 'pending';

            if ($status == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $donationStatus = 'pending';
                    } else {
                        $donationStatus = 'success';
                    }
                }
            } else if ($status == 'settlement') {
                $donationStatus = 'success';
            } else if ($status == 'pending') {
                $donationStatus = 'pending';
            } else if ($status == 'deny' || $status == 'expire' || $status == 'cancel') {
                $donationStatus = 'failed';
            }

            // Update Donation
            $this->donationRepository->update($donation->id, ['status' => $donationStatus]);

            // Update Payment details
            $this->paymentRepository->updateByDonationId($donation->id, [
                'payment_method' => $type,
                'payment_channel' => $type,
                'status' => $donationStatus,
                'external_ref' => $payload['transaction_id'] ?? null,
                'raw_response' => json_encode($payload),
                'paid_at' => $donationStatus === 'success' ? now() : null
            ]);

            // If success, Dispatch Async Job (RabbitMQ)
            if ($donationStatus === 'success') {
                ProcessSuccessfulDonation::dispatch($donation->id);
            }

            Log::info('Midtrans notification handled successfully', [
                'donation_number' => $orderId,
                'transaction_status' => $status,
                'payment_type' => $type,
                'determined_donation_status' => $donationStatus
            ]);

            return true;
        });
    }
}
