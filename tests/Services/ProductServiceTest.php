<?php
/**
 * Integration tests for App\Services\ProductService.
 *
 * These require a real MySQL connection; when one isn't available they
 * skip (not fail). Each test runs inside a transaction that is rolled
 * back in tearDown so fixtures don't leak.
 */

declare(strict_types=1);

use App\Services\ProductService;
use App\Models\Product;
use App\Core\Database;

final class ProductServiceTest extends TestCase
{
    public function testValidateProductDataAcceptsValidPayload(): void
    {
        $this->requireDb();
        $svc = ProductService::getInstance();
        $result = $svc->validateProductData([
            'name' => 'Valid Product',
            'price' => 50.0,
            'status' => 'active',
            'type' => 'simple',
        ]);
        $this->assertTrue($result['valid']);
    }

    public function testValidateProductDataRejectsInvalidStatus(): void
    {
        $this->requireDb();
        $svc = ProductService::getInstance();
        $result = $svc->validateProductData([
            'name' => 'X',
            'price' => 10.0,
            'status' => 'not-a-real-status',
        ]);
        $this->assertFalse($result['valid']);
        $this->assertTrue(isset($result['errors']['status']));
    }

    public function testValidateProductDataAcceptsAllSchemaStatuses(): void
    {
        $this->requireDb();
        $svc = ProductService::getInstance();
        foreach (['draft', 'active', 'inactive', 'out_of_stock', 'deleted'] as $status) {
            $result = $svc->validateProductData([
                'name' => 'X',
                'price' => 10.0,
                'status' => $status,
            ]);
            $this->assertTrue(
                $result['valid'],
                "Status '$status' should pass validation (it's in the schema ENUM)"
            );
        }
    }

    public function testValidateProductDataAcceptsAllSchemaTypes(): void
    {
        $this->requireDb();
        $svc = ProductService::getInstance();
        foreach (['simple', 'variable', 'bundle', 'digital'] as $type) {
            $result = $svc->validateProductData([
                'name' => 'X',
                'price' => 10.0,
                'type' => $type,
            ]);
            $this->assertTrue(
                $result['valid'],
                "Type '$type' should pass validation (it's in the schema ENUM)"
            );
        }
    }

    public function testValidateProductDataRejectsNegativePrice(): void
    {
        $this->requireDb();
        $svc = ProductService::getInstance();
        $result = $svc->validateProductData([
            'name' => 'X',
            'price' => -5.0,
        ]);
        $this->assertFalse($result['valid']);
    }

    public function testGenerateUniqueSlugProducesValidSlug(): void
    {
        $this->requireDb();
        $svc = ProductService::getInstance();
        $slug = $svc->generateUniqueSlug('Hello World Product');
        $this->assertSame('hello-world-product', $slug);
    }
}
