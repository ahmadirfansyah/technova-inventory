<?php

namespace OrderService;

class OrderRepository
{
    private string $storageFile;

    public function __construct(string $storageFile = __DIR__ . '/../data/orders.json')
    {
        $this->storageFile = $storageFile;
        $dir = dirname($this->storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!file_exists($this->storageFile)) {
            file_put_contents($this->storageFile, json_encode([]));
        }
    }

    public function all(): array
    {
        $content = file_get_contents($this->storageFile);
        return json_decode($content, true) ?? [];
    }

    public function create(int $productId, int $quantity, string $status): array
    {
        $orders = $this->all();
        $newId = empty($orders) ? 1 : max(array_column($orders, 'id')) + 1;
        $newOrder = [
            'id' => $newId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $orders[] = $newOrder;
        file_put_contents($this->storageFile, json_encode($orders, JSON_PRETTY_PRINT));
        return $newOrder;
    }
}
