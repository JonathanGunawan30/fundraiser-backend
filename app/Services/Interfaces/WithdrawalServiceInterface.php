<?php

namespace App\Services\Interfaces;

use App\Models\Withdrawal;
use Illuminate\Pagination\LengthAwarePaginator;

interface WithdrawalServiceInterface
{
    /**
     * Get all withdrawals paginated.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllWithdrawals(int $perPage): LengthAwarePaginator;

    /**
     * Get withdrawal by ID.
     *
     * @param int $id
     * @return Withdrawal
     */
    public function getWithdrawalById(int $id): Withdrawal;

    /**
     * Search withdrawals.
     *
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchWithdrawals(string $keyword, int $perPage): LengthAwarePaginator;

    /**
     * Request a new withdrawal.
     *
     * @param array $data
     * @return Withdrawal
     */
    public function requestWithdrawal(array $data): Withdrawal;

    /**
     * Process a withdrawal request (approve/reject).
     *
     * @param int $id
     * @param array $data
     * @return Withdrawal
     */
    public function processWithdrawal(int $id, array $data): Withdrawal;
}
