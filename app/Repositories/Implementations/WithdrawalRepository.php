<?php

namespace App\Repositories\Implementations;

use App\Models\Withdrawal;
use App\Repositories\Interfaces\WithdrawalRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class WithdrawalRepository implements WithdrawalRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getAllPaginated(int $perPage): LengthAwarePaginator
    {
        return Withdrawal::with(['campaign', 'user', 'processor'])->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?Withdrawal
    {
        return Withdrawal::with(['campaign', 'user', 'processor'])->find($id);
    }

    /**
     * @inheritDoc
     */
    public function search(string $keyword, int $perPage): LengthAwarePaginator
    {
        return Withdrawal::with(['campaign', 'user', 'processor'])
            ->where('bank_name', 'like', "%{$keyword}%")
            ->orWhere('account_number', 'like', "%{$keyword}%")
            ->orWhere('account_name', 'like', "%{$keyword}%")
            ->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Withdrawal
    {
        return Withdrawal::create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): Withdrawal
    {
        $withdrawal = Withdrawal::findOrFail($id);
        $withdrawal->update($data);
        return $withdrawal;
    }
}
