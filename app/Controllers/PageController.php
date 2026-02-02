<?php
declare(strict_types=1);

/**
 * Page Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Handles static pages and CMS content.
 */

namespace App\Controllers;

use App\Core\Controller;

class PageController extends Controller
{
    /**
     * Show a CMS page by slug
     */
    public function show(string $slug): void
    {
        $db = db();

        $page = $db->query("
            SELECT * FROM pages
            WHERE slug = ? AND status = 'published'
        ", [$slug])->fetch();

        if (!$page) {
            http_response_code(404);
            $this->layout('main');
            $this->view('errors/404');
            return;
        }

        $this->layout('main');
        $this->view('pages/cms-page', [
            'meta_title' => $page['meta_title'] ?: $page['title'] . ' | ' . config('app.name'),
            'meta_description' => $page['meta_description'] ?: truncate(strip_tags($page['content']), 160),
            'page' => $page,
        ]);
    }

    /**
     * Offline page for PWA
     */
    public function offline(): void
    {
        // This page is served from cache when offline
        // It's a standalone page without the main layout
        include APP_PATH . '/Views/pages/offline.php';
        exit;
    }
}
