<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateOrderRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $productId,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public readonly string $customerName,

        #[Assert\Positive]
        public readonly int $quantityOrdered,
    ) {
    }
}
