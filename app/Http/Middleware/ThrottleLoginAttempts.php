<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLoginAttempts
{
    /**
     * The rate limiter instance.
     */
    protected RateLimiter $limiter;

    /**
     * Create a new middleware instance.
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 5, int $decayMinutes = 1)
    {
        if (! $this->limiter->tooManyAttempts(
            $this->resolveRequestSignature($request), 
            $maxAttempts
        )) {
            return $this->addHeaders(
                $next($request),
                $maxAttempts,
                $this->calculateRemainingAttempts($request, $maxAttempts)
            );
        }

        return $this->buildResponse(
            $this->resolveRequestSignature($request),
            $maxAttempts,
            $decayMinutes
        );
    }

    /**
     * Resolve the request signature.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        // Use IP address + email for login throttling
        $email = $request->input('email', '');
        return 'login|' . $request->ip() . '|' . $email;
    }

    /**
     * Calculate the number of remaining attempts.
     */
    protected function calculateRemainingAttempts(Request $request, int $maxAttempts): int
    {
        return max(0, $maxAttempts - $this->limiter->attempts(
            $this->resolveRequestSignature($request)
        ));
    }

    /**
     * Add the limit headers to the response.
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts): Response
    {
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remainingAttempts);

        return $response;
    }

    /**
     * Build the "too many attempts" response.
     */
    protected function buildResponse(string $key, int $maxAttempts, int $decayMinutes): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        $response = response()->view('errors.429', [], 429);

        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', 0);
        $response->headers->set('Retry-After', $retryAfter);

        return $response;
    }
}
