<?php
/**
 * Home Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();

        // Get home sections
        $stmt = $db->query("SELECT * FROM home_sections WHERE is_active = 1 ORDER BY sort_order");
        $sections = $stmt->fetchAll();

        // Get hero banners
        $stmt = $db->query("
            SELECT * FROM banners
            WHERE location = 'hero' AND is_active = 1
            AND (starts_at IS NULL OR starts_at <= NOW())
            AND (expires_at IS NULL OR expires_at > NOW())
            ORDER BY sort_order
        ");
        $heroBanners = $stmt->fetchAll();

        // Get featured categories
        $featuredCategories = Category::featured(6);

        // Get trending products
        $trendingProducts = Product::trending(8);

        // Get new arrivals
        $newArrivals = Product::newArrivals(8);

        // Get best sellers
        $bestSellers = Product::featured(8);

        // Get on sale products
        $onSaleProducts = Product::onSale(8);

        // Get active flash sale (if any)
        $flashSale = $this->getActiveFlashSale($db);

        // Get testimonials
        $stmt = $db->query("SELECT * FROM settings WHERE `group` = 'testimonials'");
        $testimonials = [];

        $this->layout('main');
        $this->view('pages/home', [
            'meta_title' => config('seo.defaults.title'),
            'meta_description' => config('seo.defaults.description'),
            'sections' => $sections,
            'heroBanners' => $heroBanners,
            'featuredCategories' => $featuredCategories,
            'trendingProducts' => $trendingProducts,
            'newArrivals' => $newArrivals,
            'bestSellers' => $bestSellers,
            'onSaleProducts' => $onSaleProducts,
            'flashSale' => $flashSale,
            'testimonials' => $testimonials,
        ]);
    }

    /**
     * Get active flash sale
     */
    private function getActiveFlashSale($db): ?array
    {
        $stmt = $db->query("
            SELECT * FROM promotions
            WHERE type = 'flash_sale'
            AND is_active = 1
            AND starts_at <= NOW()
            AND ends_at > NOW()
            ORDER BY ends_at ASC
            LIMIT 1
        ");

        return $stmt->fetch() ?: null;
    }
}
