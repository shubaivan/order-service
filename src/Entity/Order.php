<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
class Order
{
    public const STATUS_PROCESSING = 'Processing';
    public const STATUS_REJECTED = 'Rejected';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false)]
    private Product $product;

    #[ORM\Column(type: 'string', length: 255)]
    private string $customerName;

    #[ORM\Column(type: 'integer')]
    private int $quantityOrdered;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status;

    public function __construct(Product $product, string $customerName, int $quantityOrdered, string $status = self::STATUS_PROCESSING)
    {
        $this->id = Uuid::v4();
        $this->product = $product;
        $this->customerName = $customerName;
        $this->quantityOrdered = $quantityOrdered;
        $this->status = $status;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function getQuantityOrdered(): int
    {
        return $this->quantityOrdered;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
