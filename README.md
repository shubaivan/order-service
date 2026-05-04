# order-service

Symfony 6.4 microservice — manages orders against a **local product mirror**. Validates stock and decrements quantities in a single DB transaction. Consumes `ProductSyncMessage` from the product service to keep its mirror in sync, and publishes `OrderPlacedMessage` after every successful order so the catalog master can decrement too.

Part of a four-repo system. See [`tech-task-stack`](https://github.com/shubaivan/tech-task-stack) for the full architecture, live URLs, and end-to-end test recipe.

## Endpoints

| Method | Path | Purpose |
|---|---|---|
| `POST` | `/orders`      | Place an order |
| `GET`  | `/orders`      | List all orders |
| `GET`  | `/orders/{id}` | Show one order |

## Live URL

https://orders.shuba.dev — TLS-enabled, hit it directly with `curl`.

## Request / response examples

### POST `/orders` — place an order

Request:

```json
{
  "productId": "d5dad408-01fb-468d-9477-c651ec3ebb55",
  "customerName": "John Doe",
  "quantityOrdered": 2
}
```

Response — `201 Created`:

```json
{
  "orderId": "6325cb4e-4099-47ab-936e-28ba29040e80",
  "product": {
    "id": "d5dad408-01fb-468d-9477-c651ec3ebb55",
    "name": "Coffee Mug",
    "price": 12.99,
    "quantity": 98
  },
  "customerName": "John Doe",
  "quantityOrdered": 2,
  "orderStatus": "Processing"
}
```

The embedded `product.quantity` is the remaining stock **after** the order — already decremented in the same DB transaction that persisted the order.

Errors:

- `404` — product not found in the mirror.
- `400` — insufficient stock. Body: `{"detail":"not enough stock: requested N, available M"}`.

### GET `/orders` — list

Response — `200 OK`:

```json
{
  "data": [
    {
      "orderId": "6325cb4e-4099-47ab-936e-28ba29040e80",
      "product": {
        "id": "d5dad408-01fb-468d-9477-c651ec3ebb55",
        "name": "Coffee Mug",
        "price": 12.99,
        "quantity": 98
      },
      "customerName": "John Doe",
      "quantityOrdered": 2,
      "orderStatus": "Processing"
    }
  ]
}
```

### GET `/orders/{id}` — show

Response — `200 OK`:

```json
{
  "orderId": "6325cb4e-4099-47ab-936e-28ba29040e80",
  "product": {
    "id": "d5dad408-01fb-468d-9477-c651ec3ebb55",
    "name": "Coffee Mug",
    "price": 12.99,
    "quantity": 98
  },
  "customerName": "John Doe",
  "quantityOrdered": 2,
  "orderStatus": "Processing"
}
```

Errors: `400` on invalid UUID, `404` if not found.

## Try it

```bash
curl -s -X POST https://orders.shuba.dev/orders \
  -H 'Content-Type: application/json' \
  -d '{"productId":"<uuid-from-product-service>","customerName":"John Doe","quantityOrdered":2}'
```

A product must exist in the catalog **and** have replicated into this service's mirror first (usually within a few hundred ms of `POST /products`).

## Run locally

This service expects RabbitMQ + PostgreSQL from [`tech-task-stack`](https://github.com/shubaivan/tech-task-stack) on the shared `application` docker network.

```bash
cd docker && docker compose up -d
# service then available at http://orders.loc
```

Prereqs: `127.0.0.1 orders.loc` in `/etc/hosts`, the `application` docker network created, the stack from `tech-task-stack` already up.

To consume product-sync messages locally:

```bash
docker compose exec php bin/console messenger:consume product_sync -vv
```

## Key files

- `src/Controller/OrderController.php` — HTTP endpoints
- `src/Service/OrderPlacement.php` — transactional stock validation + decrement + order persist + post-commit publish
- `src/Entity/Order.php` — order entity with `Processing`/`Rejected` status
- `src/Entity/Product.php` — local mirror, extends `Shared\Entity\ProductBase`
- `src/MessageHandler/ProductSyncHandler.php` — consumes catalog changes into the mirror
- `config/packages/messenger.yaml` — AMQP transport config

## Tech

PHP 8.3 · Symfony 6.4 · Doctrine ORM 3 · PostgreSQL · RabbitMQ via Symfony Messenger
