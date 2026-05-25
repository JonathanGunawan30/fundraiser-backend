<?php

namespace Tests\Unit\Services;

use App\Models\Donation;
use App\Models\DonationPayment;
use App\Models\User;
use App\Repositories\Interfaces\DonationPaymentRepositoryInterface;
use App\Repositories\Interfaces\DonationRepositoryInterface;
use App\Services\Implementations\DonationService;
use App\Services\Implementations\MidtransService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;

class DonationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_get_donation_by_number_returns_donation()
    {
        $donation = new Donation(['donation_number' => 'DON-123']);
        
        $this->mock(DonationRepositoryInterface::class, function (MockInterface $mock) use ($donation) {
            $mock->shouldReceive('findByNumber')->once()->with('DON-123')->andReturn($donation);
        });

        $service = app(DonationService::class);
        $result = $service->getDonationByNumber('DON-123');

        $this->assertSame($donation, $result);
    }

    public function test_get_donation_by_number_throws_exception_when_not_found()
    {
        $this->mock(DonationRepositoryInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('findByNumber')->once()->with('DON-999')->andReturn(null);
        });

        $this->expectException(ModelNotFoundException::class);

        $service = app(DonationService::class);
        $service->getDonationByNumber('DON-999');
    }

    /**
     * Test various Midtrans status scenarios.
     */
    #[DataProvider('midtransStatusProvider')]
    public function test_handle_midtrans_notification_scenarios($transactionStatus, $paymentType, $fraudStatus, $expectedDonationStatus)
    {
        $donation = Donation::factory()->create(['donation_number' => 'DON-WEBHOOK', 'status' => 'pending']);
        
        $this->mock(DonationRepositoryInterface::class, function (MockInterface $mock) use ($donation, $expectedDonationStatus) {
            $mock->shouldReceive('findByNumber')->andReturn($donation);
            $mock->shouldReceive('update')->once()->with($donation->id, ['status' => $expectedDonationStatus]);
        });

        $this->mock(DonationPaymentRepositoryInterface::class, function (MockInterface $mock) use ($donation, $expectedDonationStatus) {
            $mock->shouldReceive('updateByDonationId')->once()->with($donation->id, Mockery::on(function($arg) use ($expectedDonationStatus) {
                return $arg['status'] === $expectedDonationStatus;
            }));
        });

        $payload = [
            'order_id' => 'DON-WEBHOOK',
            'transaction_status' => $transactionStatus,
            'payment_type' => $paymentType,
            'fraud_status' => $fraudStatus,
            'transaction_id' => 'tx-123'
        ];

        $service = app(DonationService::class);
        $result = $service->handleMidtransNotification($payload);

        $this->assertTrue($result);
    }

    public static function midtransStatusProvider(): array
    {
        return [
            'Settlement is success' => ['settlement', 'bank_transfer', 'accept', 'success'],
            'Capture with accept is success' => ['capture', 'credit_card', 'accept', 'success'],
            'Capture with challenge is pending' => ['capture', 'credit_card', 'challenge', 'pending'],
            'Deny is failed' => ['deny', 'bank_transfer', 'accept', 'failed'],
            'Cancel is failed' => ['cancel', 'bank_transfer', 'accept', 'failed'],
            'Expire is failed' => ['expire', 'bank_transfer', 'accept', 'failed'],
            'Pending is pending' => ['pending', 'bank_transfer', 'accept', 'pending'],
        ];
    }
}
