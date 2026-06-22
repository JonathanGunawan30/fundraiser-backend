<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$payload = [
    'order_id' => 'DON-LESKZCFAVR',
    'transaction_status' => 'settlement',
    'payment_type' => 'qris',
    'fraud_status' => 'accept',
    'transaction_id' => 'mock-trans-123456789'
];
$service = app(App\Services\Interfaces\DonationServiceInterface::class);
$success = $service->handleMidtransNotification($payload);
echo "Result: " . ($success ? "Success" : "Failed") . PHP_EOL;
