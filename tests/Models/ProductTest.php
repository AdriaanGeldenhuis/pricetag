<?php
/**
 * Unit tests for App\Models\Product.
 *
 * Covers the pure-logic methods that operate on already-loaded attributes:
 * stock status, in-stock check, and discount calculation. These are the
 * functions most likely to silently regress and don't need a database to
 * exercise.
 */

declare(strict_types=1);

use App\Models\Product;

final class ProductTest extends TestCase
{
    private function makeProduct(array $attrs): Product
    {
        $defaults = [
            'sku' => 'TEST-001',
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100.0,
            'manage_stock' => 0,
            'stock_quantity' => 0,
            'low_stock_threshold' => 5,
            'backorders_allowed' => 0,
            'compare_price' => null,
        ];
        return new Product(array_merge($defaults, $attrs));
    }

    public function testIsInStockReturnsTrueWhenStockNotManaged(): void
    {
        $p = $this->makeProduct(['manage_stock' => 0, 'stock_quantity' => 0]);
        $this->assertTrue($p->isInStock());
    }

    public function testIsInStockReturnsTrueWhenManagedAndPositive(): void
    {
        $p = $this->makeProduct(['manage_stock' => 1, 'stock_quantity' => 3]);
        $this->assertTrue($p->isInStock());
    }

    public function testIsInStockReturnsFalseWhenManagedAndZero(): void
    {
        $p = $this->makeProduct(['manage_stock' => 1, 'stock_quantity' => 0]);
        $this->assertFalse($p->isInStock());
    }

    public function testStockStatusInStockWhenUnmanaged(): void
    {
        $p = $this->makeProduct(['manage_stock' => 0]);
        $this->assertSame('In Stock', $p->getStockStatus());
    }

    public function testStockStatusLowStockMessageWhenAtOrBelowThreshold(): void
    {
        $p = $this->makeProduct(['manage_stock' => 1, 'stock_quantity' => 3, 'low_stock_threshold' => 5]);
        $this->assertSame('Only 3 left', $p->getStockStatus());
    }

    public function testStockStatusOutOfStockWhenZeroAndBackordersForbidden(): void
    {
        $p = $this->makeProduct(['manage_stock' => 1, 'stock_quantity' => 0, 'backorders_allowed' => 0]);
        $this->assertSame('Out of Stock', $p->getStockStatus());
    }

    public function testStockStatusBackorderWhenZeroAndBackordersAllowed(): void
    {
        $p = $this->makeProduct(['manage_stock' => 1, 'stock_quantity' => 0, 'backorders_allowed' => 1]);
        $this->assertSame('Backorder', $p->getStockStatus());
    }

    public function testDiscountPercentageNullWhenNoComparePrice(): void
    {
        $p = $this->makeProduct(['price' => 100.0, 'compare_price' => null]);
        $this->assertNull($p->getDiscountPercentage());
    }

    public function testDiscountPercentageNullWhenComparePriceNotHigher(): void
    {
        $p = $this->makeProduct(['price' => 100.0, 'compare_price' => 100.0]);
        $this->assertNull($p->getDiscountPercentage());
    }

    public function testDiscountPercentageRoundsCorrectly(): void
    {
        // (200 - 150) / 200 = 25%
        $p = $this->makeProduct(['price' => 150.0, 'compare_price' => 200.0]);
        $this->assertSame(25, $p->getDiscountPercentage());
    }

    public function testDiscountPercentageHandlesFractionalResult(): void
    {
        // (100 - 67) / 100 = 33%
        $p = $this->makeProduct(['price' => 67.0, 'compare_price' => 100.0]);
        $this->assertSame(33, $p->getDiscountPercentage());
    }
}
