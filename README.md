# TechNova Inventory Management

Aplikasi manajemen inventaris berbasis microservices untuk studi kasus UAS DevOps.

## Arsitektur

Terdiri dari 2 microservices independen yang berkomunikasi via REST API:

- **inventory-service** (port 8081) — mengelola data produk dan stok.
- **order-service** (port 8082) — membuat pesanan; memanggil inventory-service untuk cek & mengurangi stok.

Setiap service punya unit test (PHPUnit), metrics endpoint (`/metrics` — format Prometheus), dan Dockerfile masing-masing.

## Menjalankan Secara Lokal (Docker Compose)

```bash
docker compose up -d --build
```

Setelah semua service jalan:

| Service | URL |
|---|---|
| Inventory Service | http://localhost:8081/products |
| Order Service | http://localhost:8082/orders |
| Prometheus | http://localhost:9090 |
| Grafana | http://localhost:3000 (login: admin/admin) |
| Loki (via Grafana Explore) | http://localhost:3100 |

## Contoh Pemakaian API

**Lihat semua produk:**
```bash
curl http://localhost:8081/products
```

**Buat pesanan baru:**
```bash
curl -X POST http://localhost:8082/orders \
  -H "Content-Type: application/json" \
  -d '{"product_id": 1, "quantity": 3}'
```

## Menjalankan Unit Test

```bash
cd inventory-service && composer install && ./vendor/bin/phpunit
cd order-service && composer install && ./vendor/bin/phpunit
```

## CI/CD

Pipeline didefinisikan di `Jenkinsfile`: clone → install dependencies → unit test (paralel) → SonarQube scan → build image → deploy via docker compose.

## Tim

- Akbar Dwi Apriantoro (24.01.5178)
- Ahmad Irfansyah (24.01.5179)
- M. Ridwan Nurdiyanto (24.01.5183)
