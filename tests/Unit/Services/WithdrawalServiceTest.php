<?php

namespace Tests\Unit\Services;

use App\Models\Campaign;
use App\Models\Withdrawal;
use App\Repositories\Interfaces\WithdrawalRepositoryInterface;
use App\Services\Implementations\WithdrawalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class WithdrawalServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_withdrawal_validates_balance_and_status()
    {
        $user = \App\Models\User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'collected_amount' => 100000,
            'status' => 'active'
        ]);

        $data = [
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'amount' => 150000, // invalid
            'bank_name' => 'BCA',
            'account_number' => '123',
            'account_name' => 'Budi'
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('exceeds available balance');

        $service = app(WithdrawalService::class);
        $service->requestWithdrawal($data);
    }

    public function test_request_withdrawal_prevents_multiple_pending()
    {
        $user = \App\Models\User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'collected_amount' => 1000000,
            'status' => 'active'
        ]);

        Withdrawal::create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'amount' => 100000,
            'bank_name' => 'BCA',
            'account_number' => '123',
            'account_name' => 'Budi',
            'status' => 'pending'
        ]);

        $data = [
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'amount' => 100000,
            'bank_name' => 'BCA',
            'account_number' => '123',
            'account_name' => 'Budi'
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('already a pending withdrawal request');

        $service = app(WithdrawalService::class);
        $service->requestWithdrawal($data);
    }
}
