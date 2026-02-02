<?php
/**
 * Application Entry Point
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * All requests are routed through this file.
 */

declare(strict_types=1);

// Load bootstrap
require_once dirname(__DIR__) . '/bootstrap.php';

// Load routes
$router = require_once APP_PATH . '/routes.php';

// Dispatch request
$router->dispatch();
