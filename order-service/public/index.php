<?php

require __DIR__ . '/../vendor/autoload.php';

use OrderService\InventoryClient;
use OrderService\OrderRepository;
use OrderService\OrderService as OrderServiceLogic;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\APC;

header('Content-Type: application/json');

$inventoryClient = new InventoryClient();
$orderRepository = new OrderRepository();
$orderService = new OrderServiceLogic($inventoryClient, $orderRepository);

// === Prometheus metrics setup ===
$registry = new CollectorRegistry(new APC());
$requestCounter = $registry->getOrRegisterCounter(
    'order_service',
    'http_requests_total',
    'Total HTTP requests handled by order-service',
    ['method', 'endpoint']
);
$orderCounter = $registry->getOrRegisterCounter(
    'order_service',
    'orders_total',
    'Total orders placed',
    ['status']
);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/metrics') {
    header('Content-Type: text/plain; version=0.0.4');
    $renderer = new Prometheus\RenderTextFormat();
    echo $renderer->render($registry->getMetricFamilySamples());
    exit;
}

$requestCounter->inc(['method' => $method, 'endpoint' => $path]);

// GET /orders - list semua pesanan
if ($method === 'GET' && $path === '/orders') {
    echo json_encode($orderRepository->all());
    exit;
}

// POST /orders - buat pesanan baru
if ($method === 'POST' && $path === '/orders') {
    $data = json_decode(file_get_contents('php://input'), true);
    $result = $orderService->placeOrder((int)$data['product_id'], (int)$data['quantity']);

    $orderCounter->inc(['status' => $result['success'] ? 'success' : 'failed']);

    http_response_code($result['success'] ? 201 : 400);
    echo json_encode($result);
    exit;
}

// Health check
if ($method === 'GET' && $path === '/health') {
    echo json_encode(['status' => 'ok', 'service' => 'order-service']);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Route not found']);
