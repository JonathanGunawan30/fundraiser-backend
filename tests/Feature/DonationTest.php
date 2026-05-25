<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use App\Services\Implementations\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessSuccessfulDonation;
use App\Mail\DonationReceiptMail;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_initiate_donation()
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create();

        // Mock Midtrans
        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('createSnapToken')->once()->andReturn((object)[
                'token' => 'mock-snap-token',
                'redirect_url' => 'https://mock-redirect.com'
            ]);
        });

        $data = [
            'campaign_id' => $campaign->id,
            'amount' => 50000,
            'message' => 'Semangat!',
            'is_anonymous' => false
        ];

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/auth/donations', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'amount' => 50000,
                    'payment' => [
                        'snap_token' => 'mock-snap-token'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('donations', ['amount' => 50000, 'user_id' => $user->id]);
    }

    public function test_midtrans_webhook_updates_status_to_success()
    {
        Queue::fake();
        
        $campaign = Campaign::factory()->create(['collected_amount' => 0]);
        $donation = Donation::create([
            'donation_number' => 'DON-TEST123',
            'campaign_id' => $campaign->id,
            'amount' => 100000,
            'status' => 'pending'
        ]);

        \App\Models\DonationPayment::create([
            'donation_id' => $donation->id,
            'gross_amount' => 100000,
            'status' => 'pending'
        ]);

        $payload = [
            'order_id' => $donation->donation_number,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'fraud_status' => 'accept',
            'transaction_id' => 'midtrans-tx-123'
        ];

        $response = $this->postJson(route('webhooks.midtrans'), $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'status' => 'success'
        ]);

        $this->assertDatabaseHas('donation_payments', [
            'donation_id' => $donation->id,
            'status' => 'success',
            'external_ref' => 'midtrans-tx-123'
        ]);

        Queue::assertPushed(ProcessSuccessfulDonation::class);
    }

    public function test_process_donation_job_updates_campaign_total_and_generates_invoice()
    {
        Storage::fake('r2');
        \Illuminate\Support\Facades\Mail::fake();

        $user = User::factory()->create(['email' => 'donor@example.com']);
        $campaign = Campaign::factory()->create(['collected_amount' => 0, 'donor_count' => 0]);
        $donation = Donation::create([
            'donation_number' => 'DON-JOB-TEST',
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'amount' => 50000,
            'status' => 'success'
        ]);

        $job = new ProcessSuccessfulDonation($donation->id);
        $job->handle();

        $campaign->refresh();
        $donation->refresh();

        $this->assertEquals(50000, $campaign->collected_amount);
        $this->assertEquals(1, $campaign->donor_count);
        
        $this->assertNotNull($donation->invoice_url);
        Storage::disk('r2')->assertExists('invoices/' . $donation->donation_number . '.pdf');

        \Illuminate\Support\Facades\Mail::assertSent(DonationReceiptMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
}
