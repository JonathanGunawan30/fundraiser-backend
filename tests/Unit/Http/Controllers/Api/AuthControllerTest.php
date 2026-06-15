<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Http\Controllers\Api\AuthController;
use App\Http\Requests\Api\LoginRequest;
use App\Services\Interfaces\AuthServiceInterface;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

use App\Http\Requests\Api\AdminOtpRequest;

class AuthControllerTest extends TestCase
{
    public function test_request_otp_success()
    {
        $data = ['email' => 'admin@test.com'];
        
        $request = Mockery::mock(AdminOtpRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($data);

        $this->mock(AuthServiceInterface::class, function (MockInterface $mock) use ($data) {
            $mock->shouldReceive('requestOtp')
                ->once()
                ->with($data);
        });

        $controller = app(AuthController::class);
        $response = $controller->requestOtp($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('OTP sent successfully to your email', $response->getContent());
    }

    public function test_admin_login_success()
    {
        $credentials = ['email' => 'admin@test.com', 'otp' => '123456'];
        
        $request = Mockery::mock(LoginRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($credentials);

        $this->mock(AuthServiceInterface::class, function (MockInterface $mock) use ($credentials) {
            $mock->shouldReceive('adminLogin')
                ->once()
                ->with($credentials)
                ->andReturn(['token' => 'admin-token']);
        });

        $controller = app(AuthController::class);
        $response = $controller->adminLogin($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Login successful', $response->getContent());
    }

    public function test_logout_success()
    {
        $this->mock(AuthServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('logout')->once();
        });

        $controller = app(AuthController::class);
        $response = $controller->logout();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Logout successful', $response->getContent());
    }
}
