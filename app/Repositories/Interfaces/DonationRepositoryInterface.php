<?php

namespace App\Repositories\Interfaces;

use App\Models\Donation;
use Illuminate\Pagination\LengthAwarePaginator;

interface DonationRepositoryInterface
{
    public function getAllPaginated(int $perPage): LengthAwarePaginator;
    public function findById(int $id): ?Donation;
    public function findByNumber(string $number): ?Donation;
    public function create(array $data): Donation;
    public function update(int $id, array $data): Donation;
}
