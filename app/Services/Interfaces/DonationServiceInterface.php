<?php

namespace App\Services\Interfaces;

use App\Models\Donation;
use Illuminate\Pagination\LengthAwarePaginator;

interface DonationServiceInterface
{
    public function getAllDonations(int $perPage): LengthAwarePaginator;
    public function getDonationByNumber(string $number): Donation;
    public function createDonation(array $data): Donation;
    public function handleMidtransNotification(array $payload): bool;
}
