<?php

namespace OrderService;

class InventoryClient
{
    private string $baseUrl;

    public function __construct(?string $baseUrl = null)
    {
        // Bisa dioverride via environment variable INVENTORY_SERVICE_URL
        // (berguna untuk docker-compose, nama service dipakai sebagai hostname)
        $this->baseUrl = $baseUrl ?? getenv('INVENTORY_SERVICE_URL') ?: 'http://localhost:8081';
    }

    public function getProduct(int $productId): ?array
    {
        $response = $this->httpGet("/products/{$productId}");
        return $response;
    }

    public function reduceStock(int $productId, int $quantity): bool
    {
        $response = $this->httpPost("/products/{$productId}/reduce-stock", ['quantity' => $quantity]);
        return isset($response['success']) && $response['success'] === true;
    }

    private function httpGet(string $path): ?array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $result === false) {
            return null;
        }
        return json_decode($result, true);
    }

    private function httpPost(string $path, array $data): ?array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            return null;
        }
        return json_decode($result, true);
    }
}
