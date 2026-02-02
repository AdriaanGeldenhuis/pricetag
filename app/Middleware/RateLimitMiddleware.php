<?php
/**
 * Rate Limiting Middleware
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Middleware;

class RateLimitMiddleware
{
    private string $key;
    private int $maxRequests;
    private int $window;

    public function __construct(string $key = 'default', ?int $maxRequests = null, ?int $window = null)
    {
        $this->key = $key;
        $this->maxRequests = $maxRequests ?? config('app.rate_limiting.requests', 60);
        $this->window = $window ?? config('app.rate_limiting.window', 60);
    }

    public function handle(): bool
    {
        if (!config('app.rate_limiting.enabled', true)) {
            return true;
        }

        $ip = clientIp();
        $cacheKey = "rate_limit:{$this->key}:{$ip}";
        $cacheFile = STORAGE_PATH . '/cache/' . md5($cacheKey) . '.rate';

        $data = $this->getCache($cacheFile);
        $now = time();

        // Clean old entries
        $data = array_filter($data, fn($timestamp) => $timestamp > ($now - $this->window));

        // Check if limit exceeded
        if (count($data) >= $this->maxRequests) {
            $retryAfter = min($data) + $this->window - $now;

            header("Retry-After: $retryAfter");
            http_response_code(429);

            if (isAjax()) {
                jsonResponse([
                    'error' => 'Too many requests',
                    'retry_after' => $retryAfter,
                ], 429);
            }

            exit('Too many requests. Please try again later.');
        }

        // Add current request
        $data[] = $now;
        $this->setCache($cacheFile, $data);

        // Add rate limit headers
        $remaining = max(0, $this->maxRequests - count($data));
        header("X-RateLimit-Limit: {$this->maxRequests}");
        header("X-RateLimit-Remaining: $remaining");
        header("X-RateLimit-Reset: " . ($now + $this->window));

        return true;
    }

    private function getCache(string $file): array
    {
        if (!file_exists($file)) {
            return [];
        }

        $content = file_get_contents($file);
        return $content ? json_decode($content, true) : [];
    }

    private function setCache(string $file, array $data): void
    {
        file_put_contents($file, json_encode($data), LOCK_EX);
    }
}
