<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Order;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Shared\Message\OrderPlacedMessage;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final class OrderPlacement
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $products,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function place(Uuid $productId, string $customerName, int $quantityOrdered): Order
    {
        $order = $this->em->wrapInTransaction(function () use ($productId, $customerName, $quantityOrdered) {
            $product = $this->products->findOneById($productId);
            if (null === $product) {
                throw new NotFoundHttpException('product not found');
            }

            if ($product->getQuantity() < $quantityOrdered) {
                throw new BadRequestHttpException(sprintf('not enough stock: requested %d, available %d', $quantityOrdered, $product->getQuantity()));
            }

            $product->setQuantity($product->getQuantity() - $quantityOrdered);
            $order = new Order($product, $customerName, $quantityOrdered);

            $this->em->persist($order);
            $this->em->flush();

            return $order;
        });

        $this->messageBus->dispatch(new OrderPlacedMessage(
            productId: (string) $order->getProduct()->getId(),
            quantityOrdered: $order->getQuantityOrdered(),
        ));

        return $order;
    }
}
