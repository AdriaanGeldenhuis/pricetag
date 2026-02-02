<?php
/**
 * Application Entry Point
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * All requests are routed through this file.
 * This file is for shared hosting where public_html is the web root.
 */

declare(strict_types=1);

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';

// Load routes
$router = require_once APP_PATH . '/routes.php';

// Dispatch request
$router->dispatch();
