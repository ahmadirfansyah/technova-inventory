<?php

namespace Inventory;

class ProductRepository
{
    private string $storageFile;

    public function __construct(string $storageFile = __DIR__ . '/../data/products.json')
    {
        $this->storageFile = $storageFile;
        if (!file_exists($this->storageFile)) {
            $dir = dirname($this->storageFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $this->seed();
        }
    }

    private function seed(): void
    {
        $initial = [
            ['id' => 1, 'name' => 'Keyboard Mechanical', 'stock' => 25, 'price' => 450000],
            ['id' => 2, 'name' => 'Mouse Wireless', 'stock' => 40, 'price' => 150000],
            ['id' => 3, 'name' => 'Monitor 24 inch', 'stock' => 10, 'price' => 1800000],
        ];
        $this->save($initial);
    }

    public function all(): array
    {
        if (!file_exists($this->storageFile)) {
            return [];
        }
        $content = file_get_contents($this->storageFile);
        return json_decode($content, true) ?? [];
    }

    public function find(int $id): ?array
    {
        foreach ($this->all() as $product) {
            if ($product['id'] === $id) {
                return $product;
            }
        }
        return null;
    }

    public function create(string $name, int $stock, int $price): array
    {
        $products = $this->all();
        $newId = empty($products) ? 1 : max(array_column($products, 'id')) + 1;
        $newProduct = ['id' => $newId, 'name' => $name, 'stock' => $stock, 'price' => $price];
        $products[] = $newProduct;
        $this->save($products);
        return $newProduct;
    }

    public function reduceStock(int $id, int $quantity): bool
    {
        $products = $this->all();
        foreach ($products as &$product) {
            if ($product['id'] === $id) {
                if ($product['stock'] < $quantity) {
                    return false;
                }
                $product['stock'] -= $quantity;
                $this->save($products);
                return true;
            }
        }
        return false;
    }

    private function save(array $products): void
    {
        file_put_contents($this->storageFile, json_encode($products, JSON_PRETTY_PRINT));
    }
}
