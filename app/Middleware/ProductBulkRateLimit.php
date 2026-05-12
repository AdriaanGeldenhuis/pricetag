<?php
/**
 * Product Bulk Rate Limit
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Throttles per-IP access to admin bulk product endpoints (bulk status
 * change, bulk price update, bulk delete). A misbehaving script could
 * otherwise hammer the database with mass UPDATE/DELETE statements.
 */

namespace App\Middleware;

class ProductBulkRateLimit
{
    private RateLimitMiddleware $inner;

    public function __construct()
    {
        // 20 requests per minute per IP.
        $this->inner = new RateLimitMiddleware('product-bulk', 20, 60);
    }

    public function handle(): bool
    {
        return $this->inner->handle();
    }
}
