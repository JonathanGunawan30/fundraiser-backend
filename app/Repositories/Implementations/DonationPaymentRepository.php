<?php

namespace App\Repositories\Implementations;

use App\Models\DonationPayment;
use App\Repositories\Interfaces\DonationPaymentRepositoryInterface;

class DonationPaymentRepository implements DonationPaymentRepositoryInterface
{
    public function create(array $data): DonationPayment
    {
        return DonationPayment::create($data);
    }

    public function updateByDonationId(int $donationId, array $data): DonationPayment
    {
        $payment = DonationPayment::where('donation_id', $donationId)->firstOrFail();
        $payment->update($data);
        return $payment;
    }

    public function findByExternalRef(string $ref): ?DonationPayment
    {
        return DonationPayment::where('external_ref', $ref)->first();
    }
}
