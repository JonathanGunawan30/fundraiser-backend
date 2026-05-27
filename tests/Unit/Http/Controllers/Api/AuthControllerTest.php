<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Http\Controllers\Api\AuthController;
use App\Http\Requests\Api\LoginRequest;
use App\Services\Interfaces\AuthServiceInterface;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function test_admin_login_success()
    {
        $credentials = ['email' => 'admin@test.com', 'password' => 'password'];
        
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
