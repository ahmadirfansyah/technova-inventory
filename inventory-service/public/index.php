<?php

require __DIR__ . '/../vendor/autoload.php';

use Inventory\ProductRepository;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\APC;

header('Content-Type: application/json');

$repo = new ProductRepository();

// === Prometheus metrics setup ===
$registry = new CollectorRegistry(new APC());
$requestCounter = $registry->getOrRegisterCounter(
    'inventory_service',
    'http_requests_total',
    'Total HTTP requests handled by inventory-service',
    ['method', 'endpoint']
);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

// Endpoint /metrics untuk di-scrape Prometheus
if ($path === '/metrics') {
    header('Content-Type: text/plain; version=0.0.4');
    $renderer = new Prometheus\RenderTextFormat();
    echo $renderer->render($registry->getMetricFamilySamples());
    exit;
}

$requestCounter->inc(['method' => $method, 'endpoint' => $path]);

// GET /products - list semua produk
if ($method === 'GET' && $path === '/products') {
    echo json_encode($repo->all());
    exit;
}

// GET /products/{id} - detail satu produk
if ($method === 'GET' && count($segments) === 2 && $segments[0] === 'products') {
    $product = $repo->find((int)$segments[1]);
    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        exit;
    }
    echo json_encode($product);
    exit;
}

// POST /products - buat produk baru
if ($method === 'POST' && $path === '/products') {
    $data = json_decode(file_get_contents('php://input'), true);
    $product = $repo->create($data['name'], (int)$data['stock'], (int)$data['price']);
    http_response_code(201);
    echo json_encode($product);
    exit;
}

// POST /products/{id}/reduce-stock - kurangi stok (dipanggil oleh order-service)
if ($method === 'POST' && count($segments) === 3 && $segments[0] === 'products' && $segments[2] === 'reduce-stock') {
    $data = json_decode(file_get_contents('php://input'), true);
    $success = $repo->reduceStock((int)$segments[1], (int)$data['quantity']);
    if (!$success) {
        http_response_code(400);
        echo json_encode(['error' => 'Insufficient stock or product not found']);
        exit;
    }
    echo json_encode(['success' => true]);
    exit;
}

// Health check
if ($method === 'GET' && $path === '/health') {
    echo json_encode(['status' => 'ok', 'service' => 'inventory-service']);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Route not found']);
