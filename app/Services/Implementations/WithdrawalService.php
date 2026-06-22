<?php

namespace App\Services\Implementations;

use App\Models\Withdrawal;
use App\Models\Campaign;
use App\Repositories\Interfaces\WithdrawalRepositoryInterface;
use App\Services\Interfaces\WithdrawalServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class WithdrawalService implements WithdrawalServiceInterface
{
    protected WithdrawalRepositoryInterface $withdrawalRepository;

    public function __construct(WithdrawalRepositoryInterface $withdrawalRepository)
    {
        $this->withdrawalRepository = $withdrawalRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllWithdrawals(int $perPage): LengthAwarePaginator
    {
        return $this->withdrawalRepository->getAllPaginated($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getWithdrawalById(int $id): Withdrawal
    {
        $withdrawal = $this->withdrawalRepository->findById($id);

        if (!$withdrawal) {
            throw new ModelNotFoundException("Withdrawal with ID {$id} not found.");
        }

        return $withdrawal;
    }

    /**
     * @inheritDoc
     */
    public function searchWithdrawals(string $keyword, int $perPage): LengthAwarePaginator
    {
        return $this->withdrawalRepository->search($keyword, $perPage);
    }

    /**
     * @inheritDoc
     */
    public function requestWithdrawal(array $data): Withdrawal
    {
        return DB::transaction(function () use ($data) {
            $campaign = Campaign::findOrFail($data['campaign_id']);

            // 1. Ownership check
            if ($campaign->user_id !== $data['user_id']) {
                throw new \RuntimeException("You are not authorized to request withdrawal for this campaign.", 403);
            }

            // 2. Status check
            if (!in_array($campaign->status, ['active', 'completed'])) {
                throw new \RuntimeException("Withdrawal can only be requested for active or completed campaigns.", 400);
            }

            // 3. Pending request check
            $pendingRequest = Withdrawal::where('campaign_id', $campaign->id)
                ->where('status', 'pending')
                ->exists();
            if ($pendingRequest) {
                throw new \RuntimeException("There is already a pending withdrawal request for this campaign.", 400);
            }

            // 4. Balance check
            $availableBalance = $campaign->collected_amount - $campaign->withdrawals()->where('status', 'completed')->sum('amount');
            if ($data['amount'] > $availableBalance) {
                throw new \RuntimeException("Requested amount exceeds available balance. Available: " . number_format($availableBalance), 400);
            }

            $data['status'] = 'pending';
            $withdrawal = $this->withdrawalRepository->create($data);

            // ponytail: notify active admins of new withdrawal request
            $admins = \App\Models\Admin::where('is_active', true)->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\WithdrawalRequestedNotification($withdrawal));
            }

            return $withdrawal;
        });
    }

    /**
     * @inheritDoc
     */
    public function processWithdrawal(int $id, array $data): Withdrawal
    {
        return DB::transaction(function () use ($id, $data) {
            $withdrawal = $this->getUpdateById($id); // Should use findById

            if ($withdrawal->status !== 'pending') {
                throw new \RuntimeException("This withdrawal request has already been processed.", 400);
            }

            if ($data['status'] === 'completed') {
                if (!isset($data['transfer_proof']) || !($data['transfer_proof'] instanceof UploadedFile)) {
                    throw new \RuntimeException("Transfer proof image is required to complete the withdrawal.", 422);
                }

                $filename = Str::uuid() . '.' . $data['transfer_proof']->getClientOriginalExtension();
                $path = $data['transfer_proof']->storeAs('withdrawals/proofs', $filename, 'r2');
                $data['transfer_proof_url'] = Storage::disk('r2')->url($path);
                $data['processed_at'] = now();
                unset($data['transfer_proof']);
            } elseif ($data['status'] === 'rejected') {
                if (!isset($data['rejection_reason'])) {
                    throw new \RuntimeException("Rejection reason is required.", 422);
                }
                $data['processed_at'] = now();
            }

            $updatedWithdrawal = $this->withdrawalRepository->update($id, $data);

            if ($updatedWithdrawal->user) {
                $updatedWithdrawal->user->notify(new \App\Notifications\WithdrawalProcessedNotification($updatedWithdrawal));
            }

            return $updatedWithdrawal;
        });
    }

    // Fixed a naming issue from previous implementation
    protected function getUpdateById(int $id) {
        return $this->getWithdrawalById($id);
    }
}
