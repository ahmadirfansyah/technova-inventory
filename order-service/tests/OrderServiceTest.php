<?php

namespace Tests;

use OrderService\InventoryClient;
use OrderService\OrderRepository;
use OrderService\OrderService;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    private string $testFile;

    protected function setUp(): void
    {
        $this->testFile = __DIR__ . '/../data/test_orders.json';
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }

    public function testPlaceOrderSuccessWhenStockAvailable(): void
    {
        // Mock InventoryClient: produk ada, stok cukup
        $mockClient = $this->createMock(InventoryClient::class);
        $mockClient->method('getProduct')->willReturn(['id' => 1, 'name' => 'Keyboard', 'stock' => 25]);
        $mockClient->method('reduceStock')->willReturn(true);

        $repo = new OrderRepository($this->testFile);
        $service = new OrderService($mockClient, $repo);

        $result = $service->placeOrder(1, 5);

        $this->assertTrue($result['success']);
        $this->assertEquals('success', $result['order']['status']);
    }

    public function testPlaceOrderFailsWhenProductNotFound(): void
    {
        $mockClient = $this->createMock(InventoryClient::class);
        $mockClient->method('getProduct')->willReturn(null);

        $repo = new OrderRepository($this->testFile);
        $service = new OrderService($mockClient, $repo);

        $result = $service->placeOrder(999, 5);

        $this->assertFalse($result['success']);
        $this->assertNull($result['order']);
        $this->assertStringContainsString('tidak ditemukan', $result['message']);
    }

    public function testPlaceOrderFailsWhenStockInsufficient(): void
    {
        $mockClient = $this->createMock(InventoryClient::class);
        $mockClient->method('getProduct')->willReturn(['id' => 1, 'name' => 'Keyboard', 'stock' => 2]);
        $mockClient->method('reduceStock')->willReturn(false);

        $repo = new OrderRepository($this->testFile);
        $service = new OrderService($mockClient, $repo);

        $result = $service->placeOrder(1, 100);

        $this->assertFalse($result['success']);
        $this->assertEquals('failed', $result['order']['status']);
    }
}
