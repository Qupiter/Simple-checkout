<?php

namespace App\Entity;

use App\Entity\Enums\OrderStatus;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\OrderRepository')]
#[ORM\Table(name: 'orders')]
class Order
{
    use Entity;

    #[ORM\Column(type: 'integer')]
    private int $totalPrice;

    #[ORM\Column(type: 'json')]
    private array $discountBreakdown;

    #[ORM\Column(type: 'string', enumType: OrderStatus::class)]
    private OrderStatus $status;

    public function __construct(int $totalPrice, array $discountBreakdown)
    {
        $this->totalPrice = $totalPrice;
        $this->discountBreakdown = $discountBreakdown;
        $this->status = OrderStatus::CREATED; // Default status
    }

    public function getTotalPrice(): int
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(int $totalPrice): self
    {
        $this->totalPrice = $totalPrice;
        return $this;
    }

    public function getDiscountBreakdown(): array
    {
        return $this->discountBreakdown;
    }

    public function setDiscountBreakdown(array $discountBreakdown): self
    {
        $this->discountBreakdown = $discountBreakdown;
        return $this;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function setStatus(OrderStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function serialize(): array
    {
        return [
            'id' => $this->getId(),
            'status' => $this->status->name,
            'totalPrice' => $this->totalPrice,
            'discountBreakdown' => $this->discountBreakdown,
        ];
    }
}