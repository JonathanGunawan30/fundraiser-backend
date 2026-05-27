<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Http\Controllers\Api\UserAuthController;
use App\Services\Interfaces\AuthServiceInterface;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Illuminate\Http\JsonResponse;

class UserAuthControllerTest extends TestCase
{
    public function test_redirect_to_provider()
    {
        $provider = 'google';
        
        $socialiteDriver = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $socialiteDriver->shouldReceive('stateless')->once()->andReturnSelf();
        $socialiteDriver->shouldReceive('redirect')->once()->andReturn(new \Symfony\Component\HttpFoundation\RedirectResponse('https://google.com'));

        Socialite::shouldReceive('driver')->with($provider)->once()->andReturn($socialiteDriver);

        $controller = app(UserAuthController::class);
        $response = $controller->redirectToProvider($provider);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\RedirectResponse::class, $response);
    }

    public function test_handle_provider_callback_success()
    {
        $provider = 'google';
        $socialUser = Mockery::mock('Laravel\Socialite\Two\User');
        
        $socialiteDriver = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $socialiteDriver->shouldReceive('stateless')->once()->andReturnSelf();
        $socialiteDriver->shouldReceive('user')->once()->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with($provider)->once()->andReturn($socialiteDriver);

        $this->mock(AuthServiceInterface::class, function (MockInterface $mock) use ($provider, $socialUser) {
            $mock->shouldReceive('userSocialLogin')
                ->once()
                ->with($provider, $socialUser)
                ->andReturn(['user' => [], 'token' => 'mock-token']);
        });

        $controller = app(UserAuthController::class);
        $response = $controller->handleProviderCallback($provider);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Login successful', $response->getContent());
    }
}
