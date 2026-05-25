<?php

namespace App\Repositories\Interfaces;

use App\Models\DonationPayment;

interface DonationPaymentRepositoryInterface
{
    public function create(array $data): DonationPayment;
    public function updateByDonationId(int $donationId, array $data): DonationPayment;
    public function findByExternalRef(string $ref): ?DonationPayment;
}
