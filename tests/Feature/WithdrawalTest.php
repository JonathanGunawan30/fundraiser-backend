<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Campaign;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WithdrawalTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_withdrawal()
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'collected_amount' => 1000000,
            'status' => 'active'
        ]);

        $data = [
            'campaign_id' => $campaign->id,
            'amount' => 500000,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Budi Santoso'
        ];

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/withdrawals', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('withdrawals', [
            'campaign_id' => $campaign->id,
            'amount' => 500000,
            'status' => 'pending'
        ]);
    }

    public function test_user_cannot_withdraw_more_than_balance()
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'collected_amount' => 100000,
            'status' => 'active'
        ]);

        $data = [
            'campaign_id' => $campaign->id,
            'amount' => 150000, // exceeds 100k
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Budi Santoso'
        ];

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/withdrawals', $data);

        $response->assertStatus(400);
        $this->assertStringContainsString('exceeds available balance', $response->json('message'));
    }

    public function test_admin_can_approve_withdrawal_with_proof()
    {
        Storage::fake('r2');
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $withdrawal = Withdrawal::create([
            'campaign_id' => Campaign::factory()->create(['collected_amount' => 1000000])->id,
            'user_id' => User::factory()->create()->id,
            'amount' => 500000,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Budi Santoso',
            'status' => 'pending'
        ]);

        $file = UploadedFile::fake()->image('proof.jpg');
        
        $response = $this->actingAs($admin, 'admin-api')
            ->postJson("/api/auth/withdrawals/{$withdrawal->id}/process", [
                'status' => 'completed',
                'transfer_proof' => $file
            ]);

        $response->assertStatus(200);
        $withdrawal->refresh();
        $this->assertEquals('completed', $withdrawal->status);
        $this->assertNotNull($withdrawal->transfer_proof_url);
        
        $filename = basename($withdrawal->transfer_proof_url);
        Storage::disk('r2')->assertExists('withdrawals/proofs/' . $filename);
    }

    public function test_admin_can_reject_withdrawal()
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $withdrawal = Withdrawal::create([
            'campaign_id' => Campaign::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
            'amount' => 500000,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Budi Santoso',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($admin, 'admin-api')
            ->postJson("/api/auth/withdrawals/{$withdrawal->id}/process", [
                'status' => 'rejected',
                'rejection_reason' => 'Data bank tidak valid'
            ]);

        $response->assertStatus(200);
        $withdrawal->refresh();
        $this->assertEquals('rejected', $withdrawal->status);
        $this->assertEquals('Data bank tidak valid', $withdrawal->rejection_reason);
    }
}
