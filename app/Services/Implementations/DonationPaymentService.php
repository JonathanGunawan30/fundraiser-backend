<?php

namespace App\Services\Implementations;

use App\Models\DonationPayment;
use App\Repositories\Interfaces\DonationPaymentRepositoryInterface;
use App\Services\Interfaces\DonationPaymentServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class DonationPaymentService implements DonationPaymentServiceInterface
{
    protected DonationPaymentRepositoryInterface $donationPaymentRepository;

    public function __construct(DonationPaymentRepositoryInterface $donationPaymentRepository)
    {
        $this->donationPaymentRepository = $donationPaymentRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllDonationPayments(int $perPage): LengthAwarePaginator
    {
        return $this->donationPaymentRepository->getAllPaginated($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getDonationPaymentById(int $id): DonationPayment
    {
        $donationPayment = $this->donationPaymentRepository->findById($id);

        if (!$donationPayment) {
            Log::warning('Donation payment lookup failed: Payment not found', ['donation_payment_id' => $id]);
            throw new ModelNotFoundException("Donation payment with ID {$id} not found.");
        }

        return $donationPayment;
    }

    /**
     * @inheritDoc
     */
    public function searchDonationPayments(string $keyword, int $perPage): LengthAwarePaginator
    {
        return $this->donationPaymentRepository->search($keyword, $perPage);
    }
}
