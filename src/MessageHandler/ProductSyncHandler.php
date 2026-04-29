<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Shared\Message\ProductSyncMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ProductSyncHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $products,
    ) {
    }

    public function __invoke(ProductSyncMessage $message): void
    {
        $dto = $message->product;
        $existing = $this->products->findOneById($dto->uuid());

        if (null === $existing) {
            $this->em->persist(new Product(
                name: $dto->name,
                price: $dto->price,
                quantity: $dto->quantity,
                id: $dto->uuid(),
            ));
        } else {
            $existing->setName($dto->name);
            $existing->setPrice($dto->price);
            $existing->setQuantity($dto->quantity);
        }

        $this->em->flush();
    }
}
