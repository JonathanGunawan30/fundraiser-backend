<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $requestId = (string) Str::uuid();

        // Share the request ID and basic context with all logs in this lifecycle
        Log::share([
            'request_id' => $requestId,
            'ip' => $request->ip(),
            'method' => $request->method(),
            'path' => $request->path(),
        ]);

        $response = $next($request);

        // Add the Request ID to the response headers for tracing
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $response
     * @return void
     */
    public function terminate(Request $request, $response)
    {
        // Resolve authenticated user if any (checking both API and Admin API guards)
        $userId = auth('api')->id() ?? auth('admin-api')->id() ?? 'guest';
        $userRole = auth('api')->check() ? 'user' : (auth('admin-api')->check() ? 'admin' : 'guest');

        // Exclude sensitive credentials from inputs log
        $input = $request->except([
            'password', 
            'password_confirmation', 
            'otp',
            'token',
            'access_token'
        ]);

        Log::info('API request handled successfully', [
            'user_id' => $userId,
            'user_role' => $userRole,
            'status_code' => $response->getStatusCode(),
            'execution_time_ms' => round((microtime(true) - LARAVEL_START) * 1000),
            'input' => $input,
        ]);
    }
}
