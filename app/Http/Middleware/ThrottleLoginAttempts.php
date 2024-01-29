<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLoginAttempts
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next, $maxAttempts = 3, $decayMinutes = 30)
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $this->fireLockoutEvent($request);
            $seconds = $this->limiter->availableIn($key);

            return $this->responseAfterAttemptsExceeded($seconds);
        }

        $response = $next($request);

        if ($response->getStatusCode() === Response::HTTP_UNAUTHORIZED) {
            $this->limiter->hit($key, $decayMinutes * 60);
        }

        return $response;
    }

    protected function resolveRequestSignature(Request $request)
    {
        return sha1(
            $request->method() .
            '|' . $request->server('SERVER_NAME') .
            '|' . $request->path() .
            '|' . $request->ip()
        );
    }

    protected function responseAfterAttemptsExceeded($seconds)
    {
        $message = 'Too many login attempts. Please try again after ' . ceil($seconds / 60) . ' minutes.';
        return response()->json(['message' => $message], Response::HTTP_TOO_MANY_REQUESTS);
    }

    protected function fireLockoutEvent(Request $request)
    {
        event(new \Illuminate\Auth\Events\Lockout($request));
    }
}
