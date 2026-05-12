<?php
/**
 * Product AI Rate Limit
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Throttles per-IP access to expensive product AI endpoints (OpenAI content
 * generation, web image search). The base RateLimitMiddleware accepts the
 * key/limit/window via constructor; the Router only instantiates middleware
 * with `new $class()`, so a thin wrapper is the simplest way to bind the
 * configuration.
 */

namespace App\Middleware;

class ProductAiRateLimit
{
    private RateLimitMiddleware $inner;

    public function __construct()
    {
        // 10 requests per 5 minutes per IP.
        $this->inner = new RateLimitMiddleware('product-ai', 10, 300);
    }

    public function handle(): bool
    {
        return $this->inner->handle();
    }
}
