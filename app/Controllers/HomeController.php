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

        // Get home sections (graceful fallback if table doesn't exist)
        $sections = [];
        try {
            $stmt = $db->query("SELECT * FROM home_sections WHERE is_active = 1 ORDER BY sort_order");
            $sections = $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Table doesn't exist yet, use empty array
        }

        // Get hero banners (graceful fallback if table doesn't exist)
        $heroBanners = [];
        try {
            $stmt = $db->query("
                SELECT * FROM banners
                WHERE location = 'hero' AND is_active = 1
                AND (starts_at IS NULL OR starts_at <= NOW())
                AND (expires_at IS NULL OR expires_at > NOW())
                ORDER BY sort_order
            ");
            $heroBanners = $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Table doesn't exist yet, use empty array
        }

        // Get featured categories
        $featuredCategories = Category::featured(6);

        // Get ALL categories for the quick category bar
        $categories = Category::all();

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
        $testimonials = [];

        // Get promo banners (homepage_middle location)
        $promoBanners = [];
        try {
            $stmt = $db->query("
                SELECT * FROM banners
                WHERE location = 'homepage_middle' AND is_active = 1
                AND (starts_at IS NULL OR starts_at <= NOW())
                AND (expires_at IS NULL OR expires_at > NOW())
                ORDER BY sort_order
                LIMIT 4
            ");
            $promoBanners = $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Table doesn't exist yet
        }

        $this->layout('main');
        $this->view('pages/home', [
            'meta_title' => config('seo.defaults.title'),
            'meta_description' => config('seo.defaults.description'),
            'sections' => $sections,
            'heroBanners' => $heroBanners,
            'categories' => $categories,
            'featuredCategories' => $featuredCategories,
            'trendingProducts' => $trendingProducts,
            'newArrivals' => $newArrivals,
            'bestSellers' => $bestSellers,
            'onSaleProducts' => $onSaleProducts,
            'flashSale' => $flashSale,
            'testimonials' => $testimonials,
            'promoBanners' => $promoBanners,
        ]);
    }

    /**
     * Get active flash sale
     */
    private function getActiveFlashSale($db): ?array
    {
        try {
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
        } catch (\PDOException $e) {
            // Table doesn't exist yet
            return null;
        }
    }
}
