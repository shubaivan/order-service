<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CreateOrderRequest;
use App\Dto\OrderResponse;
use App\Repository\OrderRepository;
use App\Service\OrderPlacement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/orders')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderPlacement $placement,
        private readonly OrderRepository $orders,
    ) {
    }

    #[Route('', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateOrderRequest $request): JsonResponse
    {
        $order = $this->placement->place(
            Uuid::fromString($request->productId),
            $request->customerName,
            $request->quantityOrdered,
        );

        return new JsonResponse(OrderResponse::fromEntity($order), 201);
    }
}
