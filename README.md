# order-service

Symfony 6.4 microservice — places orders. Keeps a local mirror of products by consuming `ProductSyncMessage` from RabbitMQ. Validates stock and decrements `quantity` inside a single transaction when an order is placed.

Endpoints:

- `POST /orders` — place an order
- `GET /orders` — list
- `GET /orders/{id}` — fetch one
- `GET /api/doc` — Swagger UI

## Run locally

```bash
docker compose up -d
composer install
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n
symfony serve -d --port=8001

# in another terminal — start the RabbitMQ consumer
php bin/console messenger:consume product_sync -vv
```

## Try it

After publishing a product from `product-service`:

```bash
curl -X POST http://127.0.0.1:8001/orders \
  -H 'Content-Type: application/json' \
  -d '{"productId":"<uuid-from-product-service>","customerName":"John Doe","quantityOrdered":2}'
```

Response:

```json
{
  "orderId": "01904c7e-...",
  "product": { "id": "...", "name": "Coffee Mug", "price": 12.99, "quantity": 98 },
  "customerName": "John Doe",
  "quantityOrdered": 2,
  "orderStatus": "Processing"
}
```

If stock is insufficient or the product is unknown, the API responds with `400` / `404`.
