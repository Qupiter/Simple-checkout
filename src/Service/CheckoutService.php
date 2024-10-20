<?php

namespace App\Service;

use App\Entity\Collections\RuleCollection;
use App\Entity\Enums\OrderStatus;
use App\Entity\Order;
use App\Entity\Product;
use App\Repository\OrderRepository;
use App\Service\Collections\CartCollection;
use App\Service\Exceptions\OrderCanceledException;
use App\Service\Exceptions\OrderCompletedException;
use Doctrine\ORM\EntityManagerInterface;

class CheckoutService
{
    private Order $order;
    private CartCollection $cartCollection;
    private RuleCollection $ruleCollection;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly  OrderRepository $orderRepository
    ) {
        $this->cartCollection = new CartCollection();
    }

    public function setBulkPriceRules(RuleCollection $rules): void
    {
        $this->ruleCollection = $rules;
    }

    public function getOrder(int $id): Order
    {
        return $this->orderRepository->findOneBy(['id' => $id]);
    }

    public function scanProduct(Product $product): void
    {
        $this->cartCollection->addItem($product);
    }

    public function scanCollection(CartCollection $cartCollection): void
    {
        $this->cartCollection->mergeCollections($cartCollection);
    }

    public function clearCart(): void
    {
        $this->cartCollection->clear();
    }

    public function generateBreakdown(): void
    {
        $total = 0;
        $discountBreakdown = [];

        /** @var Product $productData */
        foreach ($this->cartCollection as $productData) {
            $item = $productData['item'];
            $quantity = $productData['quantity'];

            // Find the matching pricing rule for the item
            $rule = $this->ruleCollection->findRuleForProduct($item);

            if ($rule) {
                // Apply bulk pricing rule
                $discountedPrice = $rule->calculatePrice($quantity);
                $total += $discountedPrice;
            } else {
                // Default pricing (no bulk rule)
                $total += $item->getPrice() * $quantity;
            }

            $discountBreakdown[] = [
                'product' => $item->getSku(),
                'quantity' => $quantity,
                'regularPrice' => $item->getPrice() * $quantity,
                'discountedPrice' => $discountedPrice ?? 0,
                'appliedRule' => $rule?->serialize() ?? null,
            ];
        }

        $this->order = new Order($total, $discountBreakdown);
    }

    public function saveOrder(): Order
    {
        // calculate total and breakdown
        $this->generateBreakdown();
        $this->order->setCreatedAtValue();

        $this->entityManager->persist($this->order);
        $this->entityManager->flush();

        return $this->order;
    }

    /**
     * @throws OrderCanceledException
     */
    public function completeOrder(Order $order): void
    {
        if($order->getStatus() === OrderStatus::CANCELED)
            throw new OrderCanceledException();

        // change status
        $order->setStatus(OrderStatus::COMPLETED);
        $order->setUpdatedAtValue();

        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }

    /**
     * @throws OrderCompletedException
     */
    public function cancelOrder(Order $order): void
    {
        if($order->getStatus() === OrderStatus::COMPLETED)
            throw new OrderCompletedException();

        // change status
        $order->setStatus(OrderStatus::CANCELED);
        $order->setUpdatedAtValue();

        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }
}