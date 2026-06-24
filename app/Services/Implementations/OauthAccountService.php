<?php

namespace App\Services\Implementations;

use App\Models\OauthAccount;
use App\Repositories\Interfaces\OauthAccountRepositoryInterface;
use App\Services\Interfaces\OauthAccountServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class OauthAccountService implements OauthAccountServiceInterface
{
    protected OauthAccountRepositoryInterface $oauthAccountRepository;

    public function __construct(OauthAccountRepositoryInterface $oauthAccountRepository)
    {
        $this->oauthAccountRepository = $oauthAccountRepository;
    }

    /**
     * @inheritDoc
     */
    public function getAllOauthAccounts(int $perPage): LengthAwarePaginator
    {
        return $this->oauthAccountRepository->getAllPaginated($perPage);
    }

    /**
     * @inheritDoc
     */
    public function getOauthAccountById(int $id): OauthAccount
    {
        $oauthAccount = $this->oauthAccountRepository->findById($id);

        if (!$oauthAccount) {
            Log::warning('OAuth Account lookup failed: Account not found', ['oauth_account_id' => $id]);
            throw new ModelNotFoundException("OAuth Account with ID {$id} not found.");
        }

        return $oauthAccount;
    }

    /**
     * @inheritDoc
     */
    public function searchOauthAccounts(string $keyword, int $perPage): LengthAwarePaginator
    {
        return $this->oauthAccountRepository->search($keyword, $perPage);
    }
}
