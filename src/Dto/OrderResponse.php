<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Order;
use Shared\Dto\ProductDto;

final class OrderResponse implements \JsonSerializable
{
    public function __construct(
        public readonly string $orderId,
        public readonly ProductDto $product,
        public readonly string $customerName,
        public readonly int $quantityOrdered,
        public readonly string $orderStatus,
    ) {
    }

    public static function fromEntity(Order $order): self
    {
        return new self(
            orderId: (string) $order->getId(),
            product: ProductDto::fromEntity($order->getProduct()),
            customerName: $order->getCustomerName(),
            quantityOrdered: $order->getQuantityOrdered(),
            orderStatus: $order->getStatus(),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'orderId' => $this->orderId,
            'product' => $this->product,
            'customerName' => $this->customerName,
            'quantityOrdered' => $this->quantityOrdered,
            'orderStatus' => $this->orderStatus,
        ];
    }
}
