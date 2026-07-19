<?php

namespace Tests;

use Inventory\ProductRepository;
use PHPUnit\Framework\TestCase;

class ProductRepositoryTest extends TestCase
{
    private ProductRepository $repo;
    private string $testFile;

    protected function setUp(): void
    {
        $this->testFile = __DIR__ . '/../data/test_products.json';
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
        $this->repo = new ProductRepository($this->testFile);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }

    public function testSeedDataExists(): void
    {
        $products = $this->repo->all();
        $this->assertCount(3, $products);
    }

    public function testFindExistingProduct(): void
    {
        $product = $this->repo->find(1);
        $this->assertNotNull($product);
        $this->assertEquals('Keyboard Mechanical', $product['name']);
    }

    public function testFindNonExistingProduct(): void
    {
        $product = $this->repo->find(999);
        $this->assertNull($product);
    }

    public function testCreateProduct(): void
    {
        $product = $this->repo->create('Webcam HD', 15, 350000);
        $this->assertEquals('Webcam HD', $product['name']);
        $this->assertEquals(15, $product['stock']);
        $this->assertCount(4, $this->repo->all());
    }

    public function testReduceStockSuccess(): void
    {
        $result = $this->repo->reduceStock(1, 5);
        $this->assertTrue($result);
        $product = $this->repo->find(1);
        $this->assertEquals(20, $product['stock']);
    }

    public function testReduceStockInsufficientFails(): void
    {
        $result = $this->repo->reduceStock(1, 999);
        $this->assertFalse($result);
    }

    public function testReduceStockNonExistingProductFails(): void
    {
        $result = $this->repo->reduceStock(999, 1);
        $this->assertFalse($result);
    }
}
