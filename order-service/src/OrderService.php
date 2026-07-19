<?php

namespace OrderService;

class OrderService
{
    private InventoryClient $inventoryClient;
    private OrderRepository $orderRepository;

    public function __construct(InventoryClient $inventoryClient, OrderRepository $orderRepository)
    {
        $this->inventoryClient = $inventoryClient;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Membuat pesanan baru.
     * Alur: cek produk ada -> cek & kurangi stok via inventory-service -> simpan order.
     *
     * @return array{success: bool, order: ?array, message: string}
     */
    public function placeOrder(int $productId, int $quantity): array
    {
        $product = $this->inventoryClient->getProduct($productId);

        if ($product === null) {
            return ['success' => false, 'order' => null, 'message' => 'Produk tidak ditemukan di inventory-service'];
        }

        $stockReduced = $this->inventoryClient->reduceStock($productId, $quantity);

        if (!$stockReduced) {
            $order = $this->orderRepository->create($productId, $quantity, 'failed');
            return ['success' => false, 'order' => $order, 'message' => 'Stok tidak mencukupi'];
        }

        $order = $this->orderRepository->create($productId, $quantity, 'success');
        return ['success' => true, 'order' => $order, 'message' => 'Pesanan berhasil dibuat'];
    }
}
