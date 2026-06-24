<?php 

use App\Http\Controllers\Api\UserAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'data' => [
            'name' => config('app.name'),
            'version' => '1.0.0',
            'php_version' => PHP_VERSION,
        ],
        'message' => 'FundRaiser API is online'
    ]);
});

Route::prefix('auth/user/{provider}')->group(function () {
    Route::get('redirect', [UserAuthController::class, 'redirectToProvider']);
    Route::get('callback', [UserAuthController::class, 'handleProviderCallback']);
})->where('provider', 'google|github');
