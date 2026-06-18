<?php

namespace App\Repositories\Implementations;

use App\Models\Donation;
use App\Repositories\Interfaces\DonationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class DonationRepository implements DonationRepositoryInterface
{
    public function getAllPaginated(int $perPage): LengthAwarePaginator
    {
        return Donation::with(['user', 'campaign'])->paginate($perPage);
    }

    public function getByUserPaginated(int $userId, int $perPage): LengthAwarePaginator
    {
        return Donation::with(['campaign'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Donation
    {
        return Donation::with(['user', 'campaign', 'payment'])->find($id);
    }

    public function findByNumber(string $number): ?Donation
    {
        return Donation::with(['user', 'campaign', 'payment'])->where('donation_number', $number)->first();
    }

    public function create(array $data): Donation
    {
        return Donation::create($data);
    }

    public function update(int $id, array $data): Donation
    {
        $donation = Donation::findOrFail($id);
        $donation->update($data);
        return $donation;
    }
}
